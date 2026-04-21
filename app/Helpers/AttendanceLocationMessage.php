<?php

namespace App\Helpers;

final class AttendanceLocationMessage
{
    public static function build(?float $latitude, ?float $longitude): array
    {
        $latitude = self::normalizeLatitude($latitude);
        $longitude = self::normalizeLongitude($longitude);

        if ($latitude === null || $longitude === null) {
            return [];
        }

        $lat = number_format($latitude, 6, '.', '');
        $lng = number_format($longitude, 6, '.', '');

        return [
            'address' => "{$lat}, {$lng}",
            'link' => "https://www.google.com/maps?q={$lat},{$lng}",
        ];
    }

    private static function normalizeLatitude(?float $latitude): ?float
    {
        if ($latitude === null) {
            return null;
        }

        if ($latitude < -90 || $latitude > 90) {
            return null;
        }

        return $latitude;
    }

    private static function normalizeLongitude(?float $longitude): ?float
    {
        if ($longitude === null) {
            return null;
        }

        if ($longitude < -180 || $longitude > 180) {
            return null;
        }

        return $longitude;
    }
}

