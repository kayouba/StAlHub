<?php
return [
    '/'             => ['App\Controller\HomeController',     'index'],
    '/login'        => ['App\Controller\AuthController',     'showLoginForm'],
    '/login/post'   => ['App\Controller\AuthController',     'login'],
    '/otp'          => ['App\Controller\OTPController',      'show'],
    '/otp/verify'   => ['App\Controller\OTPController',      'verify'],
    '/dashboard'    => ['App\Controller\DashboardController','index'],
    '/logout'       => ['App\LogoutController',              'logout'],
    '/student/new-request' => ['App\Controller\StudentController', 'newRequest'],
    '/student/request/step2' => ['App\Controller\StudentController', 'step2'],
    '/student/request/step3' => ['App\Controller\StudentController', 'step3'],
    '/student/request/step4' => ['App\Controller\StudentController', 'step4'],
    '/student/request/step5' => ['App\Controller\StudentController', 'step5'],
    '/profile'         => ['App\Controller\ProfileController', 'index'],
    '/profile/submit'  => ['App\Controller\ProfileController', 'submit'],



];
