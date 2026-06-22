<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Attendance Telegram Notifications
    |--------------------------------------------------------------------------
    |
    | Sends a Telegram message to a group after successful attendance check-in
    | / check-out. Messages are routed by the employee's department.
    |
    | Chat IDs are controlled by the Telegram Groups interface.
    */
    'enabled' => true,
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),

    // Chat routing is stored in telegram_groups.
    'default_chat_id' => null,

    'department_chat_ids' => '',

    // Legacy env routing is disabled; use telegram_groups instead.
    'rules' => '',

    // Additional recipients are also managed by telegram_groups.
    'always_chat_ids' => '',

    // Optional reverse-geocoding (to show "real address")
    'reverse_geocode_enabled' => (bool) env('ATTENDANCE_TELEGRAM_REVERSE_GEOCODE_ENABLED', false),
    'reverse_geocode_user_agent' => env('ATTENDANCE_TELEGRAM_GEOCODE_USER_AGENT', 'hr-ky-admin-attendance-bot'),

    // Optional Telegram location pin
    'send_location_enabled' => false,

    'timeout_seconds' => 3,
];
