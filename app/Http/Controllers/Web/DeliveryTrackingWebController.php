<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DeliveryTrackingWebController extends Controller
{
    /**
     * Show live public delivery tracking page for customers
     *
     * @param string $token
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function show(string $token)
    {
        $delivery = Delivery::with(['user:id,name,phone,avatar', 'branch:id,name'])
            ->where('tracking_token', $token)
            ->first();

        if (!$delivery) {
            return view('errors.404', [
                'message' => 'តំណភ្ជាប់តាមដានការដឹកជញ្ជូនមិនត្រឹមត្រូវ ឬផុតកំណត់ហើយ (Delivery tracking link not found or expired)',
            ]);
        }

        // Get latest cached position if available
        $cached = Cache::get("delivery_loc_{$delivery->id}");
        if ($cached) {
            $delivery->current_latitude = $cached['lat'] ?? $delivery->current_latitude;
            $delivery->current_longitude = $cached['lng'] ?? $delivery->current_longitude;
            $delivery->heading = $cached['heading'] ?? $delivery->heading;
            $delivery->speed = $cached['speed'] ?? $delivery->speed;
        }

        return view('public.delivery-tracking', compact('delivery'));
    }
}
