<?php

namespace App\Modules\DailyRecords;

final class BranchRouteCalculator
{
    /**
     * Calculate Haversine great-circle distance between two points in kilometers.
     */
    public static function distanceKm(?float $lat1, ?float $lon1, ?float $lat2, ?float $lon2): ?float
    {
        if ($lat1 === null || $lon1 === null || $lat2 === null || $lon2 === null) {
            return null;
        }
        if (abs($lat1 - $lat2) < 0.000001 && abs($lon1 - $lon2) < 0.000001) {
            return 0.0;
        }

        $earthRadius = 6371.0; // Earth's mean radius in kilometers
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($angle * $earthRadius, 2);
    }

    /**
     * Enrich raw route fields with chronological stops, segment distances, total km, and average speed.
     */
    public static function enrich(array $route): array
    {
        $stops = [];

        // 1. Origin / Departure Stop
        $hasStartCoords = isset($route['start_latitude'], $route['start_longitude'])
            && is_numeric($route['start_latitude']) && is_numeric($route['start_longitude']);

        $depTime = $route['departure_time'] ?? null;
        $originStop = [
            'stop_index' => 1,
            'title' => $route['origin_branch'] ?? 'Origin Branch',
            'action_type' => 'departure',
            'time_label' => $depTime ?: '—',
            'raw_time' => $depTime,
            'latitude' => $hasStartCoords ? (float) $route['start_latitude'] : null,
            'longitude' => $hasStartCoords ? (float) $route['start_longitude'] : null,
            'notes' => $route['purpose'] ?? '',
            'photo_count' => 0,
        ];
        $stops[] = $originStop;

        // 2. Checkpoints
        $checkpoints = (array) ($route['checkpoints_data'] ?? []);
        foreach ($checkpoints as $idx => $cp) {
            $hasCoords = isset($cp['latitude'], $cp['longitude'])
                && is_numeric($cp['latitude']) && is_numeric($cp['longitude']);

            $timeLabel = $cp['arrived_at'] ?? null;
            if ($timeLabel) {
                try {
                    $dt = \Carbon\Carbon::parse($timeLabel);
                    $timeLabel = $dt->format('H:i') . ' (' . $dt->format('d M') . ')';
                } catch (\Throwable) {
                    // keep raw string
                }
            }

            $stops[] = [
                'stop_index' => count($stops) + 1,
                'title' => $cp['branch_name'] ?? ('Checkpoint ' . ($idx + 1)),
                'action_type' => $cp['action_type'] ?? 'arrival',
                'time_label' => $timeLabel ?: '—',
                'raw_time' => $cp['arrived_at'] ?? null,
                'latitude' => $hasCoords ? (float) $cp['latitude'] : null,
                'longitude' => $hasCoords ? (float) $cp['longitude'] : null,
                'notes' => $cp['notes'] ?? '',
                'photo_count' => (int) ($cp['photo_count'] ?? 0),
            ];
        }

        // 3. Calculate segments between consecutive stops
        $segments = [];
        $totalDistanceKm = 0.0;

        for ($i = 0; $i < count($stops) - 1; $i++) {
            $from = $stops[$i];
            $to = $stops[$i + 1];

            $dist = self::distanceKm($from['latitude'], $from['longitude'], $to['latitude'], $to['longitude']);
            if ($dist !== null) {
                $totalDistanceKm += $dist;
            }

            $durationMin = null;
            if (!empty($from['raw_time']) && !empty($to['raw_time'])) {
                try {
                    $t1 = \Carbon\Carbon::parse($from['raw_time']);
                    $t2 = \Carbon\Carbon::parse($to['raw_time']);
                    $diff = $t1->diffInMinutes($t2);
                    if ($diff > 0 && $diff < 1440) {
                        $durationMin = $diff;
                    }
                } catch (\Throwable) {
                    // ignore parse failures
                }
            }

            $segments[] = [
                'from_index' => $from['stop_index'],
                'to_index' => $to['stop_index'],
                'from_name' => $from['title'],
                'to_name' => $to['title'],
                'distance_km' => $dist,
                'formatted_distance' => $dist !== null ? ($dist . ' km') : '—',
                'duration_minutes' => $durationMin,
                'formatted_duration' => $durationMin !== null ? self::formatMinutes($durationMin) : null,
            ];

            $stops[$i + 1]['leg_distance_km'] = $dist;
            $stops[$i + 1]['leg_duration_minutes'] = $durationMin;
            $stops[$i + 1]['formatted_leg_distance'] = $dist !== null ? ($dist . ' km') : '—';
            $stops[$i + 1]['formatted_leg_duration'] = $durationMin !== null ? self::formatMinutesKhmer($durationMin) : null;
        }

        $totalMinutes = (int) ($route['total_minutes'] ?? 0);
        $totalHours = $totalMinutes > 0 ? ($totalMinutes / 60.0) : 0.0;
        $avgSpeed = ($totalHours > 0 && $totalDistanceKm > 0) ? round($totalDistanceKm / $totalHours, 1) : null;

        $route['stops'] = $stops;
        $route['segments'] = $segments;
        $route['total_distance_km'] = round($totalDistanceKm, 2);
        $route['formatted_total_distance'] = round($totalDistanceKm, 1) . ' km';
        $route['average_speed_kmh'] = $avgSpeed;
        $route['formatted_avg_speed'] = $avgSpeed !== null ? ($avgSpeed . ' km/h') : '—';
        $route['formatted_duration_kh'] = self::formatMinutesKhmer($totalMinutes);

        return $route;
    }

    public static function formatMinutes(int $minutes): string
    {
        if ($minutes < 60) {
            return $minutes . ' min' . ($minutes !== 1 ? 's' : '');
        }
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        return $m > 0 ? "{$h}h {$m}m" : "{$h}h";
    }

    public static function formatMinutesKhmer(int $minutes): string
    {
        if ($minutes <= 0) {
            return '—';
        }
        if ($minutes < 60) {
            return $minutes . ' នាទី';
        }
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        return $m > 0 ? "{$h} ម៉ោង {$m} នាទី" : "{$h} ម៉ោង";
    }
}
