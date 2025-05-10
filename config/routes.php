<?php
return [
    '/'            => ['App\Controller\HomeController',    'index'],

    // Auth
    '/login'       => ['App\Controller\AuthController',    'showLoginForm'],
    '/login/post'  => ['App\Controller\AuthController',    'login'],

    // OTP
    '/otp'         => ['App\Controller\OTPController',     'show'],
    '/otp/verify'  => ['App\Controller\OTPController',     'verify'],

    // Dashboard (protégé)
    '/dashboard'   => ['App\Controller\DashboardController','index'],
];
