<?php

use Illuminate\Support\Str;

return [
    /** App Setting */

    'authorize-login' => 'authorize login',
    'override-bssid' => 'override bssid',
    '24-hour-format' => '24 hour format',
    'bs' => 'Date In BS',
    'attendance-note' => 'Attendance Note',

    /** General Setting */

    'firebase_key'=> 'Firebase Key',
    'firebase_key_description' => 'Firebase key is needed to send notification in mobile.',
    'attendance_notify' => 'Set Number Of Days for local Push Notification',
    'attendance_notify_description' => 'Setting no of days will automatically send the data of those days to the mobile application.Receiving this data on the mobile end will allow the mobile application to set local push notification for those dates. The local push notification will help employees remember to check in on time as well as to check out when the shift is about to end.',
    'advance_salary_limit' => 'Advance Salary Limit(%)',
    'advance_salary_limit_description' => 'Set the maximum amount in percent a employee can request in advance based on gross salary.',
    'employee_code_prefix' => 'Employee Code Prefix',
    'employee_code_prefix_description' => 'This prefix will be used to make employee code.',
    'attendance_limit' => 'Attendance Limit',
    'attendance_limit_description' => 'attendance limit for checkin and checkout.',
    'award_display_limit' => 'Award Display Limit',
    'award_display_limit_description' => 'award display limit in mobile app.',


];
