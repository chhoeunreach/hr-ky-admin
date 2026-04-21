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
    | ATTENDANCE_TELEGRAM_DEPARTMENT_CHAT_IDS accepts either:
    |  - JSON: {"1":"-100123","2":"-100456"}
    |  - CSV:  1:-100123,2:-100456
    */
    'enabled' => (bool) env('ATTENDANCE_TELEGRAM_ENABLED', false),
    'bot_token' => env('ATTENDANCE_TELEGRAM_BOT_TOKEN'),

    // Used when no department mapping exists (or department is null)
    'default_chat_id' => env('ATTENDANCE_TELEGRAM_DEFAULT_CHAT_ID'),

    // JSON or CSV string (parsed by the notifier service)
    'department_chat_ids' => env('ATTENDANCE_TELEGRAM_DEPARTMENT_CHAT_IDS', ''),

    /*
     * Optional routing rules (first match wins).
     * JSON array example:
     *  [
     *    {"department":"management","chat_id":"-1002799577548"},
     *    {"branch":"កម្ពុជាក្រោម","department":"មេឌៀ(KY)","chat_id":"-1002727901053"}
     *  ]
     */
    'rules' => env('ATTENDANCE_TELEGRAM_RULES', ''),

    /*
     * Optional additional recipients for every message.
     * Accepts JSON array (["-1001","-1002"]) or CSV (-1001,-1002).
     */
    'always_chat_ids' => env('ATTENDANCE_TELEGRAM_ALWAYS_CHAT_IDS', ''),

    // Optional reverse-geocoding (to show "real address")
    'reverse_geocode_enabled' => (bool) env('ATTENDANCE_TELEGRAM_REVERSE_GEOCODE_ENABLED', false),
    'reverse_geocode_user_agent' => env('ATTENDANCE_TELEGRAM_GEOCODE_USER_AGENT', 'hr-ky-admin-attendance-bot'),

    // Optional Telegram location pin
    'send_location_enabled' => (bool) env('ATTENDANCE_TELEGRAM_SEND_LOCATION', false),

    'timeout_seconds' => (int) env('ATTENDANCE_TELEGRAM_TIMEOUT', 3),
];
