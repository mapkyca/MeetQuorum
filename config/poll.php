<?php

return [
    'half_day' => [
        'morning_start' => env('HALF_DAY_MORNING_START', '09:00'),
        'morning_end' => env('HALF_DAY_MORNING_END', '13:00'),
        'afternoon_start' => env('HALF_DAY_AFTERNOON_START', '13:00'),
        'afternoon_end' => env('HALF_DAY_AFTERNOON_END', '17:00'),
    ],
    'window' => [
        'start' => env('APP_SLOT_WINDOW_START', '09:00'),
        'end' => env('APP_SLOT_WINDOW_END', '17:00'),
    ],
    'magic_token_days' => (int) env('VOTER_MAGIC_TOKEN_DAYS', 90),
];
