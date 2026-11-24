<?php

namespace App\Modules\DailyRecords;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class BranchRouteInput
{
    public static function validate(array $fields): array
    {
        $data = Validator::make($fields, [
            'mission_type' => 'required|in:branch_route',
            'route_id' => 'required|uuid',
            'purpose' => 'required|string|max:3000',
            'origin_branch' => 'required|string|max:180',
            'target_branches' => 'required|array|min:1|max:30',
            'target_branches.*' => 'required|string|max:180',
            'mission_mode' => 'nullable|in:round_trip,multi_branch',
            'departure_time' => 'required|date_format:H:i',
            'arrival_time' => 'nullable|date_format:H:i',
            'status' => 'required|in:completed,cancelled,in_progress,pending,ongoing',
            'total_minutes' => 'required|integer|min:0|max:525600',
            'formatted_duration' => 'nullable|string|max:80',
            'start_latitude' => 'nullable|numeric|between:-90,90',
            'start_longitude' => 'nullable|numeric|between:-180,180',
            'end_latitude' => 'nullable|numeric|between:-90,90',
            'end_longitude' => 'nullable|numeric|between:-180,180',
            'start_address' => 'nullable|string|max:500',
            'final_summary' => 'nullable|string|max:5000',
            'checkpoints_count' => 'required|integer|min:0|max:100',
            'checkpoints_data' => 'nullable|string|max:50000',
            'supporting_links' => 'nullable|array|max:20',
            'supporting_links.*' => 'required|url|max:2048',
        ])->validate();

        $checkpoints = [];
        if (!empty($data['checkpoints_data'])) {
            $decoded = json_decode($data['checkpoints_data'], true);
            if (!is_array($decoded) || !array_is_list($decoded) || count($decoded) > 100) {
                throw ValidationException::withMessages(['fields.checkpoints_data' => 'Supply a valid checkpoint list.']);
            }
            foreach ($decoded as $index => $checkpoint) {
                $point = Validator::make(is_array($checkpoint) ? $checkpoint : [], [
                    'id' => 'nullable|string|max:80',
                    'branch_name' => 'required|string|max:180',
                    'action_type' => 'required|in:departure,arrival',
                    'arrived_at' => 'required|date',
                    'latitude' => 'nullable|numeric|between:-90,90',
                    'longitude' => 'nullable|numeric|between:-180,180',
                    'notes' => 'nullable|string|max:2000',
                    'photo_paths' => 'nullable|array|max:10',
                ])->validate();
                $checkpoints[] = [
                    'id' => $point['id'] ?? (string) $index,
                    'branch_name' => $point['branch_name'],
                    'action_type' => $point['action_type'],
                    'arrived_at' => $point['arrived_at'],
                    'latitude' => isset($point['latitude']) ? (float) $point['latitude'] : null,
                    'longitude' => isset($point['longitude']) ? (float) $point['longitude'] : null,
                    'notes' => $point['notes'] ?? '',
                    'photo_count' => count($point['photo_paths'] ?? []),
                ];
            }
        }

        $data['checkpoints_data'] = $checkpoints;
        $data['supporting_links'] = array_values($data['supporting_links'] ?? []);
        foreach ($data['supporting_links'] as $link) {
            if (!in_array(strtolower((string) parse_url($link, PHP_URL_SCHEME)), ['http', 'https'], true)) {
                throw ValidationException::withMessages(['fields.supporting_links' => 'Links must use HTTP or HTTPS.']);
            }
        }
        return $data;
    }
}
