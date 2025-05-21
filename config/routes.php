<?php
return [
    '/'               => ['App\Controller\AuthController', 'landing'],
    '/login'          => ['App\Controller\AuthController', 'showLoginForm'],
    '/login/post'     => ['App\Controller\AuthController', 'login'],
    '/register'       => ['App\Controller\AuthController', 'showRegisterForm'],
    '/register/post'  => ['App\Controller\AuthController', 'register'],
    '/otp'            => ['App\Controller\OTPController', 'show'],
    '/otp/verify'     => ['App\Controller\OTPController', 'verify'],
    '/dashboard'      => ['App\Controller\DashboardController', 'index'],
    '/logout'         => ['App\Controller\LogoutController', 'logout'],

    '/student/new-request' => ['App\Controller\StudentController', 'newRequest'],
    '/student/request/step2' => ['App\Controller\StudentController', 'step2'],
    '/student/request/step3' => ['App\Controller\StudentController', 'step3'],
    '/student/request/step4' => ['App\Controller\StudentController', 'step4'],
    '/student/request/step5' => ['App\Controller\StudentController', 'step5'],
    '/student/request/view' => ['App\Controller\StudentController', 'viewRequest'],
    '/student/request/submit' => ['App\Controller\StudentController', 'submitRequest'],

    '/profile'         => ['App\Controller\ProfileController', 'index'],
    '/profile/submit'  => ['App\Controller\ProfileController', 'submit'],

    //  Ajout du dashboard admin
    '/admin/dashboard' => ['App\Controller\AdminController', 'dashboard'],
    '/admin/tab/users'      => ['App\Controller\AdminController', 'tabUsers'],
    '/admin/tab/requests'   => ['App\Controller\AdminController', 'tabRequests'],
    '/admin/tab/companies'  => ['App\Controller\AdminController', 'tabCompanies'],
    '/admin/users/updateRole' => ['App\Controller\AdminController', 'updateUserRole'],
    '/admin/users/delete' => ['App\Controller\AdminController', 'deleteUser'],
    '/admin/users/suspend' => ['App\Controller\AdminController', 'toggleActive'],
    '/admin/requests/view' => ['App\Controller\AdminController', 'viewRequest'],
    '/admin/companies/delete' => ['App\Controller\AdminController', 'deleteCompany'],
    '/admin/companies/view' => ['App\Controller\AdminController', 'viewCompany'],
    '/admin/companies/requests' => ['App\Controller\AdminController', 'getCompanyRequests'],


];

