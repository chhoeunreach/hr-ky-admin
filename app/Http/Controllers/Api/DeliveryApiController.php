<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DeliveryApiController extends Controller
{
    const PROOF_UPLOAD_PATH = 'uploads/delivery-proofs/';
    const COMPLETION_SELFIE_UPLOAD_PATH = 'uploads/delivery-completion-selfies/';

    /**
     * Get driver's current active delivery (if any)
     */
    public function active(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $delivery = Delivery::with(['user:id,name,phone,avatar', 'branch:id,name'])
                ->where('user_id', $user->id)
                ->whereIn('status', [Delivery::STATUS_PENDING, Delivery::STATUS_IN_TRANSIT, Delivery::STATUS_ARRIVED])
                ->latest()
                ->first();

            return response()->json([
                'status' => true,
                'message' => $delivery ? 'Active delivery found' : 'No active delivery',
                'status_code' => 200,
                'data' => $delivery,
            ]);
        } catch (Exception $e) {
            return AppHelper::sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * List user's deliveries (active or history)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $query = Delivery::with(['user:id,name,phone,avatar', 'branch:id,name'])
                ->where('user_id', $user->id);

            $status = $request->get('status', 'all');
            if ($status === 'active') {
                $query->whereIn('status', [Delivery::STATUS_PENDING, Delivery::STATUS_IN_TRANSIT, Delivery::STATUS_ARRIVED]);
            } elseif ($status === 'completed') {
                $query->whereIn('status', [Delivery::STATUS_DELIVERED, Delivery::STATUS_CANCELLED]);
            } elseif ($request->filled('status') && $status !== 'all') {
                $query->where('status', $status);
            }

            $perPage = (int) $request->get('per_page', 20);
            $deliveries = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'status' => true,
                'message' => __('index.data_found'),
                'status_code' => 200,
                'data' => $deliveries->items(),
                'pagination' => [
                    'current_page' => $deliveries->currentPage(),
                    'last_page' => $deliveries->lastPage(),
                    'per_page' => $deliveries->perPage(),
                    'total' => $deliveries->total(),
                    'has_more' => $deliveries->hasMorePages(),
                ],
            ]);
        } catch (Exception $e) {
            return AppHelper::sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get list of active branches for destination branch selection
     */
    public function branches(): JsonResponse
    {
        try {
            $branches = Branch::withoutGlobalScope('branch')
                ->select('id', 'name', 'address', 'branch_location_latitude', 'branch_location_longitude')
                ->orderBy('name')
                ->get();

            return response()->json([
                'status' => true,
                'data' => $branches,
            ]);
        } catch (Exception $e) {
            return AppHelper::sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Start a new delivery task and generate public tracking token
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'delivery_type' => ['nullable', 'string', 'in:customer,branch'],
                'destination_branch_id' => ['nullable', 'integer'],
                'customer_name' => ['nullable', 'string', 'max:255'],
                'customer_phone' => ['nullable', 'string', 'max:50'],
                'destination_address' => ['nullable', 'string', 'max:500'],
                'destination_latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'destination_longitude' => ['nullable', 'numeric', 'between:-180,180'],
                'origin_address' => ['nullable', 'string', 'max:500'],
                'origin_latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'origin_longitude' => ['nullable', 'numeric', 'between:-180,180'],
                'invoice_number' => ['nullable', 'string', 'max:100'],
                'notes' => ['nullable', 'string'],
                'driver_selfie' => ['required', 'image', 'max:10240'],
                'product_photos' => ['nullable', 'array', 'max:5'],
                'product_photos.*' => ['image', 'max:10240'],
            ]);

            $user = $request->user();
            $activeDelivery = Delivery::where('user_id', $user->id)
                ->whereIn('status', [
                    Delivery::STATUS_PENDING,
                    Delivery::STATUS_IN_TRANSIT,
                    Delivery::STATUS_ARRIVED,
                ])
                ->latest()
                ->first();

            if ($activeDelivery) {
                return response()->json([
                    'status' => false,
                    'message' => 'មានការដឹកជញ្ជូនកំពុងដំណើរការ។ សូមបញ្ចប់វាមុនសិន។',
                    'status_code' => 409,
                    'data' => $activeDelivery,
                ], 409);
            }
            $token = 'kny_' . Str::random(24) . '_' . time();
            $today = Carbon::today()->format('ymd');
            $countToday = Delivery::whereDate('created_at', Carbon::today())->count() + 1;
            $trackingCode = 'DEL-' . $today . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);

            // Auto-generate invoice number if empty
            $invoiceNumber = !empty($validated['invoice_number']) 
                ? trim($validated['invoice_number']) 
                : ('INV-' . $today . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT));

            $deliveryType = $validated['delivery_type'] ?? Delivery::TYPE_CUSTOMER;
            $destinationBranchId = $validated['destination_branch_id'] ?? null;
            $destLat = $validated['destination_latitude'] ?? null;
            $destLng = $validated['destination_longitude'] ?? null;
            $destAddress = $validated['destination_address'] ?? null;

            // If branch delivery, auto-fill destination coordinates and address from branch
            if ($deliveryType === Delivery::TYPE_BRANCH && $destinationBranchId) {
                $targetBranch = Branch::withoutGlobalScope('branch')->find($destinationBranchId);
                if ($targetBranch) {
                    $destLat = $destLat ?? $targetBranch->branch_location_latitude;
                    $destLng = $destLng ?? $targetBranch->branch_location_longitude;
                    $destAddress = $destAddress ?? ($targetBranch->address ?? $targetBranch->name);
                    if (empty($validated['customer_name'])) {
                        $validated['customer_name'] = 'សាខា៖ ' . $targetBranch->name;
                    }
                }
            }

            // Default to General Customer if empty
            $customerName = !empty($validated['customer_name']) 
                ? trim($validated['customer_name']) 
                : 'អតិថិជនទូទៅ (General Customer)';

            $customerPhone = !empty($validated['customer_phone']) 
                ? trim($validated['customer_phone']) 
                : '';

            $originLat = $validated['origin_latitude'] ?? null;
            $originLng = $validated['origin_longitude'] ?? null;

            // Handle Driver Selfie Upload
            $selfiePath = null;
            if ($request->hasFile('driver_selfie')) {
                $file = $request->file('driver_selfie');
                $filename = 'selfie_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $dir = public_path('uploads/deliveries/selfies');
                if (!file_exists($dir)) mkdir($dir, 0755, true);
                $file->move($dir, $filename);
                $selfiePath = 'uploads/deliveries/selfies/' . $filename;
            }

            // Handle Product Photos Upload (Multi-photos)
            $productPhotos = [];
            $photos = $request->file('product_photos');
            if ($photos) {
                if (!is_array($photos)) $photos = [$photos];
                $dir = public_path('uploads/deliveries/products');
                if (!file_exists($dir)) mkdir($dir, 0755, true);
                foreach ($photos as $idx => $photo) {
                    if ($photo instanceof \Illuminate\Http\UploadedFile) {
                        $filename = 'product_' . time() . '_' . $idx . '_' . Str::random(6) . '.' . $photo->getClientOriginalExtension();
                        $photo->move($dir, $filename);
                        $productPhotos[] = 'uploads/deliveries/products/' . $filename;
                    }
                }
            }

            $delivery = Delivery::create([
                'user_id' => $user->id,
                'branch_id' => $user->branch_id ?? null,
                'destination_branch_id' => $destinationBranchId,
                'delivery_type' => $deliveryType,
                'tracking_token' => $token,
                'tracking_code' => $trackingCode,
                'invoice_number' => $invoiceNumber,
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'destination_address' => $destAddress,
                'destination_latitude' => $destLat,
                'destination_longitude' => $destLng,
                'origin_address' => $validated['origin_address'] ?? null,
                'origin_latitude' => $originLat,
                'origin_longitude' => $originLng,
                'current_latitude' => $originLat,
                'current_longitude' => $originLng,
                'last_ping_at' => Carbon::now(),
                'status' => Delivery::STATUS_IN_TRANSIT,
                'started_at' => Carbon::now(),
                'expires_at' => Carbon::now()->addHours(48),
                'driver_selfie' => $selfiePath,
                'product_photos' => $productPhotos,
                'notes' => $validated['notes'] ?? null,
            ]);

            $delivery->load(['user:id,name,phone,avatar', 'branch:id,name', 'destinationBranch:id,name']);

            // Initialize cache
            Cache::put("delivery_loc_{$delivery->id}", [
                'lat' => $originLat,
                'lng' => $originLng,
                'heading' => 0.0,
                'speed' => 0.0,
                'status' => Delivery::STATUS_IN_TRANSIT,
                'updated_at' => Carbon::now()->toIso8601String(),
            ], 86400);

            return response()->json([
                'status' => true,
                'message' => 'ការដឹកជញ្ជូនត្រូវបានចាប់ផ្តើមដោយជោគជ័យ (Delivery started successfully)',
                'status_code' => 201,
                'data' => $delivery,
            ], 201);
        } catch (Exception $e) {
            return AppHelper::sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Driver pings live GPS location (15-30 seconds interval)
     */
    public function pingLocation(Request $request, $id = null): JsonResponse
    {
        try {
            $deliveryId = $request->input('delivery_id') ?? $id ?? $request->route('id');

            $validated = $request->validate([
                'latitude' => ['required', 'numeric', 'between:-90,90'],
                'longitude' => ['required', 'numeric', 'between:-180,180'],
                'heading' => ['nullable', 'numeric'],
                'speed' => ['nullable', 'numeric'],
            ]);

            if (!$deliveryId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Delivery ID is required',
                    'status_code' => 422,
                ], 422);
            }

            $user = $request->user();
            $delivery = Delivery::where('id', $deliveryId)
                ->where('user_id', $user->id)
                ->firstOrFail();

            if (!$delivery->is_active) {
                return response()->json([
                    'status' => false,
                    'message' => 'ការដឹកជញ្ជូននេះត្រូវបានបញ្ចប់រួចហើយ (Delivery already completed or cancelled)',
                    'status_code' => 400,
                ], 400);
            }

            $lat = (float) $validated['latitude'];
            $lng = (float) $validated['longitude'];
            $heading = isset($validated['heading']) ? (float) $validated['heading'] : null;
            $speed = isset($validated['speed']) ? (float) $validated['speed'] : null;
            $now = Carbon::now();

            // Update cache for instantaneous read without database lag
            Cache::put("delivery_loc_{$delivery->id}", [
                'lat' => $lat,
                'lng' => $lng,
                'heading' => $heading,
                'speed' => $speed,
                'status' => $delivery->status,
                'updated_at' => $now->toIso8601String(),
            ], 86400);

            // Periodically persist to DB
            $delivery->update([
                'current_latitude' => $lat,
                'current_longitude' => $lng,
                'heading' => $heading,
                'speed' => $speed,
                'last_ping_at' => $now,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'GPS ping recorded',
                'status_code' => 200,
            ]);
        } catch (Exception $e) {
            return AppHelper::sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Mark delivery as delivered (with optional proof photo)
     */
    public function complete(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $delivery = Delivery::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $validated = $request->validate([
                'proof_photo' => ['nullable', 'file', 'max:15360', 'mimes:jpg,jpeg,png,webp'],
                'completion_selfie' => ['required', 'image', 'max:10240', 'mimes:jpg,jpeg,png,webp'],
                'notes' => ['nullable', 'string'],
            ]);

            $proofPath = null;
            $completionSelfiePath = null;
            if ($request->hasFile('completion_selfie')) {
                $file = $request->file('completion_selfie');
                $ext = strtolower($file->getClientOriginalExtension());
                $dir = public_path(self::COMPLETION_SELFIE_UPLOAD_PATH);
                if (!File::isDirectory($dir)) {
                    File::makeDirectory($dir, 0755, true, true);
                }
                $filename = time() . '_' . $delivery->id . '_selfie.' . $ext;
                $file->move($dir, $filename);
                $completionSelfiePath = self::COMPLETION_SELFIE_UPLOAD_PATH . $filename;
            }

            if ($request->hasFile('proof_photo')) {
                $file = $request->file('proof_photo');
                $ext = strtolower($file->getClientOriginalExtension());
                $dir = public_path(self::PROOF_UPLOAD_PATH);
                if (!File::isDirectory($dir)) {
                    File::makeDirectory($dir, 0755, true, true);
                }
                $filename = time() . '_' . $delivery->id . '_proof.' . $ext;
                $file->move($dir, $filename);
                $proofPath = self::PROOF_UPLOAD_PATH . $filename;
            }

            $now = Carbon::now();
            $delivery->update([
                'status' => Delivery::STATUS_DELIVERED,
                'completed_at' => $now,
                'proof_photo_path' => $proofPath ?: $delivery->proof_photo_path,
                'completion_selfie' => $completionSelfiePath ?: $delivery->completion_selfie,
                'notes' => $validated['notes'] ?? $delivery->notes,
            ]);

            Cache::put("delivery_loc_{$delivery->id}", [
                'lat' => $delivery->current_latitude,
                'lng' => $delivery->current_longitude,
                'heading' => $delivery->heading,
                'speed' => 0.0,
                'status' => Delivery::STATUS_DELIVERED,
                'updated_at' => $now->toIso8601String(),
            ], 86400);

            $delivery->load(['user:id,name,phone,avatar', 'branch:id,name']);

            return response()->json([
                'status' => true,
                'message' => 'ទំនិញត្រូវបានប្រគល់ដោយជោគជ័យ (Delivered successfully)',
                'status_code' => 200,
                'data' => $delivery,
            ]);
        } catch (Exception $e) {
            return AppHelper::sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Cancel delivery
     */
    public function cancel(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $delivery = Delivery::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $delivery->update([
                'status' => Delivery::STATUS_CANCELLED,
                'completed_at' => Carbon::now(),
                'notes' => $request->input('notes', $delivery->notes),
            ]);

            Cache::forget("delivery_loc_{$delivery->id}");

            return response()->json([
                'status' => true,
                'message' => 'ការដឹកជញ្ជូនត្រូវបានបោះបង់ (Delivery cancelled)',
                'status_code' => 200,
                'data' => $delivery,
            ]);
        } catch (Exception $e) {
            return AppHelper::sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Public real-time tracking data for customer web view (no authentication required)
     */
    public function publicTrack($token): JsonResponse
    {
        try {
            $delivery = Delivery::with(['user:id,name,phone,avatar', 'branch:id,name', 'destinationBranch:id,name'])
                ->where('tracking_token', $token)
                ->first();

            if (!$delivery) {
                return response()->json([
                    'status' => false,
                    'message' => 'រកមិនឃើញទិន្នន័យនៃការដឹកជញ្ជូននេះឡើយ (Tracking link not found)',
                    'status_code' => 404,
                ], 404);
            }

            // Check cached real-time coordinates
            $cached = Cache::get("delivery_loc_{$delivery->id}");
            $currentLat = $cached['lat'] ?? $delivery->current_latitude;
            $currentLng = $cached['lng'] ?? $delivery->current_longitude;
            $heading = $cached['heading'] ?? $delivery->heading;
            $speed = $cached['speed'] ?? $delivery->speed;
            $lastPing = $cached['updated_at'] ?? ($delivery->last_ping_at ? $delivery->last_ping_at->toIso8601String() : null);

            return response()->json([
                'status' => true,
                'message' => 'Live tracking data',
                'status_code' => 200,
                'data' => [
                    'tracking_code' => $delivery->tracking_code,
                    'invoice_number' => $delivery->invoice_number,
                    'delivery_type' => $delivery->delivery_type,
                    'destination_branch_name' => $delivery->destinationBranch?->name,
                    'customer_name' => $delivery->customer_name,
                    'destination_address' => $delivery->destination_address,
                    'destination_latitude' => $delivery->destination_latitude,
                    'destination_longitude' => $delivery->destination_longitude,
                    'origin_address' => $delivery->origin_address ?? ($delivery->branch?->name ?? 'KNEAYERNG Store'),
                    'origin_latitude' => $delivery->origin_latitude,
                    'origin_longitude' => $delivery->origin_longitude,
                    'current_latitude' => $currentLat,
                    'current_longitude' => $currentLng,
                    'heading' => $heading,
                    'speed' => $speed,
                    'last_ping_at' => $lastPing,
                    'delivery_status' => $delivery->status,
                    'status_label' => $delivery->status_label,
                    'is_active' => $delivery->is_active,
                    'started_at' => $delivery->started_at ? $delivery->started_at->toIso8601String() : null,
                    'completed_at' => $delivery->completed_at ? $delivery->completed_at->toIso8601String() : null,
                    'formatted_duration' => $delivery->formatted_duration,
                    'driver_selfie_url' => $delivery->driver_selfie_url,
                    'product_photo_urls' => $delivery->product_photo_urls,
                    'proof_photo_url' => $delivery->proof_photo_url,
                    'driver' => [
                        'name' => $delivery->user?->name ?? 'អ្នកដឹកជញ្ជូន (Driver)',
                        'phone' => $delivery->user?->phone,
                        'avatar' => $delivery->driver_selfie_url ?? $delivery->user?->avatar_url,
                    ],
                ],
            ]);
        } catch (Exception $e) {
            return AppHelper::sendErrorResponse($e->getMessage(), 500);
        }
    }
}
