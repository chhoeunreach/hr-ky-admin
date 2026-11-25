<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Delivery;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class DeliveryAdminController extends Controller
{
    /**
     * Display delivery dashboard & directory
     */
    public function index(Request $request)
    {
        $today = Carbon::now('Asia/Phnom_Penh')->toDateString();

        $filters = $request->validate([
            'status' => 'nullable|string|in:all,active,pending,in_transit,arrived,delivered,cancelled',
            'date' => 'nullable|date_format:Y-m-d',
            'from' => 'nullable|date_format:Y-m-d',
            'to' => 'nullable|date_format:Y-m-d|after_or_equal:from',
            'driver_id' => 'nullable|integer',
            'branch_id' => 'nullable|integer',
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|in:10,25,50,100,all',
        ]);

        $query = Delivery::with(['user:id,name,phone,avatar', 'branch:id,name', 'destinationBranch:id,name']);

        // Filters
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            if ($filters['status'] === 'active') {
                $query->whereIn('status', [Delivery::STATUS_PENDING, Delivery::STATUS_IN_TRANSIT, Delivery::STATUS_ARRIVED]);
            } else {
                $query->where('status', $filters['status']);
            }
        }

        if (!empty($filters['date'])) {
            $query->whereDate('created_at', $filters['date']);
        }
        if (!empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }
        if (!empty($filters['driver_id'])) {
            $query->where('user_id', $filters['driver_id']);
        }
        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }
        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('tracking_code', 'like', "%{$term}%")
                    ->orWhere('invoice_number', 'like', "%{$term}%")
                    ->orWhere('customer_name', 'like', "%{$term}%")
                    ->orWhere('customer_phone', 'like', "%{$term}%")
                    ->orWhere('destination_address', 'like', "%{$term}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%"));
            });
        }

        // KPIs calculation
        $summary = [
            'total_today' => Delivery::whereDate('created_at', $today)->count(),
            'in_transit' => Delivery::where('status', Delivery::STATUS_IN_TRANSIT)->count(),
            'delivered_today' => Delivery::where('status', Delivery::STATUS_DELIVERED)->whereDate('completed_at', $today)->count(),
            'pending' => Delivery::where('status', Delivery::STATUS_PENDING)->count(),
            'active_drivers' => Delivery::whereIn('status', [Delivery::STATUS_IN_TRANSIT, Delivery::STATUS_ARRIVED])
                ->distinct('user_id')
                ->count('user_id'),
        ];

        $perPage = ($filters['per_page'] ?? '25') === 'all' ? 1000 : (int) ($filters['per_page'] ?? 25);
        $rows = $query->latest('id')->paginate($perPage)->withQueryString();

        // Enforce cached latest positions for active deliveries
        foreach ($rows as $row) {
            if ($row->is_active) {
                $cached = Cache::get("delivery_loc_{$row->id}");
                if ($cached) {
                    $row->current_latitude = $cached['lat'] ?? $row->current_latitude;
                    $row->current_longitude = $cached['lng'] ?? $row->current_longitude;
                    $row->heading = $cached['heading'] ?? $row->heading;
                    $row->speed = $cached['speed'] ?? $row->speed;
                }
            }
        }

        // Active Fleet for Live Map
        $activeFleet = Delivery::with(['user:id,name,phone,avatar', 'branch:id,name'])
            ->whereIn('status', [Delivery::STATUS_IN_TRANSIT, Delivery::STATUS_ARRIVED])
            ->latest('last_ping_at')
            ->get()
            ->map(function ($d) {
                $cached = Cache::get("delivery_loc_{$d->id}");
                if ($cached) {
                    $d->current_latitude = $cached['lat'] ?? $d->current_latitude;
                    $d->current_longitude = $cached['lng'] ?? $d->current_longitude;
                    $d->heading = $cached['heading'] ?? $d->heading;
                    $d->speed = $cached['speed'] ?? $d->speed;
                }
                return $d;
            });

        // Dropdowns for filtering and dispatching
        $drivers = User::where('is_active', true)
            ->select('id', 'name', 'phone', 'branch_id')
            ->orderBy('name')
            ->get();

        $branches = Branch::select('id', 'name')->orderBy('name')->get();

        return view('admin.deliveries.index', compact(
            'rows',
            'summary',
            'filters',
            'drivers',
            'branches',
            'activeFleet'
        ));
    }

    /**
     * Return live locations JSON of active drivers for fleet map polling
     */
    public function liveLocations(): JsonResponse
    {
        $deliveries = Delivery::with(['user:id,name,phone,avatar', 'branch:id,name'])
            ->whereIn('status', [Delivery::STATUS_IN_TRANSIT, Delivery::STATUS_ARRIVED])
            ->latest('last_ping_at')
            ->get()
            ->map(function ($d) {
                $cached = Cache::get("delivery_loc_{$d->id}");
                $lat = $cached['lat'] ?? $d->current_latitude;
                $lng = $cached['lng'] ?? $d->current_longitude;
                $heading = $cached['heading'] ?? $d->heading;
                $speed = $cached['speed'] ?? $d->speed;

                return [
                    'id' => $d->id,
                    'tracking_code' => $d->tracking_code,
                    'invoice_number' => $d->invoice_number,
                    'status' => $d->status,
                    'status_label' => $d->status_label,
                    'driver_name' => $d->user->name ?? 'Unknown',
                    'driver_phone' => $d->user->phone ?? '—',
                    'driver_avatar' => $d->user->avatar ?? null,
                    'customer_name' => $d->customer_name,
                    'customer_phone' => $d->customer_phone,
                    'destination_address' => $d->destination_address,
                    'current_latitude' => $lat,
                    'current_longitude' => $lng,
                    'heading' => $heading,
                    'speed' => $speed,
                    'destination_latitude' => $d->destination_latitude,
                    'destination_longitude' => $d->destination_longitude,
                    'origin_latitude' => $d->origin_latitude,
                    'origin_longitude' => $d->origin_longitude,
                    'last_ping_formatted' => $d->last_ping_at ? $d->last_ping_at->diffForHumans() : '—',
                    'tracking_url' => $d->tracking_url,
                ];
            });

        return response()->json([
            'status' => true,
            'count' => $deliveries->count(),
            'deliveries' => $deliveries,
        ]);
    }

    /**
     * Dispatch new delivery
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'branch_id' => 'nullable|exists:branches,id',
            'destination_branch_id' => 'nullable|exists:branches,id',
            'delivery_type' => 'required|in:customer,branch',
            'invoice_number' => 'nullable|string|max:100',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'destination_address' => 'nullable|string|max:500',
            'destination_latitude' => 'nullable|numeric|between:-90,90',
            'destination_longitude' => 'nullable|numeric|between:-180,180',
            'origin_address' => 'nullable|string|max:500',
            'origin_latitude' => 'nullable|numeric|between:-90,90',
            'origin_longitude' => 'nullable|numeric|between:-180,180',
            'notes' => 'nullable|string|max:2000',
        ]);

        $token = Str::random(32);
        $code = 'DEL-' . date('ymd') . '-' . strtoupper(Str::random(4));

        $delivery = Delivery::create([
            'user_id' => $validated['user_id'],
            'branch_id' => $validated['branch_id'] ?? null,
            'destination_branch_id' => $validated['destination_branch_id'] ?? null,
            'delivery_type' => $validated['delivery_type'],
            'tracking_token' => $token,
            'tracking_code' => $code,
            'invoice_number' => $validated['invoice_number'] ?? null,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'destination_address' => $validated['destination_address'] ?? null,
            'destination_latitude' => $validated['destination_latitude'] ?? null,
            'destination_longitude' => $validated['destination_longitude'] ?? null,
            'origin_address' => $validated['origin_address'] ?? null,
            'origin_latitude' => $validated['origin_latitude'] ?? null,
            'origin_longitude' => $validated['origin_longitude'] ?? null,
            'status' => Delivery::STATUS_PENDING,
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Delivery dispatched successfully',
                'delivery' => $delivery,
            ]);
        }

        return redirect()->route('admin.deliveries.index')
            ->with('success', 'ការចាត់ចែងការដឹកជញ្ជូនថ្មីត្រូវបានបង្កើតជោគជ័យ (Delivery created: ' . $code . ')');
    }

    /**
     * Show details for modal popup
     */
    public function show(Delivery $delivery): JsonResponse
    {
        $delivery->load(['user:id,name,phone,avatar', 'branch:id,name', 'destinationBranch:id,name']);

        $cached = Cache::get("delivery_loc_{$delivery->id}");
        if ($cached) {
            $delivery->current_latitude = $cached['lat'] ?? $delivery->current_latitude;
            $delivery->current_longitude = $cached['lng'] ?? $delivery->current_longitude;
            $delivery->heading = $cached['heading'] ?? $delivery->heading;
            $delivery->speed = $cached['speed'] ?? $delivery->speed;
        }

        return response()->json([
            'status' => true,
            'delivery' => $delivery,
        ]);
    }

    /**
     * Update delivery status
     */
    public function updateStatus(Request $request, Delivery $delivery)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_transit,arrived,delivered,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        $delivery->status = $validated['status'];
        if (!empty($validated['notes'])) {
            $delivery->notes = ($delivery->notes ? $delivery->notes . "\n" : '') . $validated['notes'];
        }

        if ($validated['status'] === Delivery::STATUS_IN_TRANSIT && !$delivery->started_at) {
            $delivery->started_at = Carbon::now();
        } elseif (in_array($validated['status'], [Delivery::STATUS_DELIVERED, Delivery::STATUS_CANCELLED])) {
            $delivery->completed_at = Carbon::now();
        }

        $delivery->save();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Status updated to ' . $delivery->status_label,
                'delivery' => $delivery,
            ]);
        }

        return redirect()->back()
            ->with('success', 'បានកែប្រែស្ថានភាពការដឹកជញ្ជូនទៅជា៖ ' . $delivery->status_label);
    }

    /**
     * Delete delivery record
     */
    public function destroy(Delivery $delivery)
    {
        $code = $delivery->tracking_code;
        $delivery->delete();

        return redirect()->route('admin.deliveries.index')
            ->with('success', "បានលុបការដឹកជញ្ជូន {$code} រួចរាល់។");
    }
}
