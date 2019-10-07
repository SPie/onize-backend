<?php

return [
    'maxLoginAttempts'        => env('SECURITY_MAX_LOGIN_ATTEMPTS', 3),
    'throttlingTimeInMinutes' => env('SECURITY_THROTTLING_TIME_IN_MINUTES', 15),
];
