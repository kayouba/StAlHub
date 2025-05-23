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
    '/logout'         => ['App\LogoutController', 'logout'],
    '/mentions-legales' => ['App\Controller\AuthController', 'mentionsLegales'],

    // Student
    '/student/new-request'      => ['App\Controller\StudentController', 'newRequest'],
    '/student/request/step2'    => ['App\Controller\StudentController', 'step2'],
    '/student/request/step3'    => ['App\Controller\StudentController', 'step3'],
    '/student/request/step4'    => ['App\Controller\StudentController', 'step4'],
    '/student/request/step5'    => ['App\Controller\StudentController', 'step5'],
    '/student/request/view'     => ['App\Controller\StudentController', 'viewRequest'],
    '/student/request/submit'   => ['App\Controller\StudentController', 'submitRequest'],

    // Tuteur
    '/tutor/dashboard'          => ['App\Controller\TutorController', 'index'],
    '/tutor/update'             => ['App\Controller\TutorController', 'updateCapacity'],
    '/tutor/students'           => ['App\Controller\TutorController', 'assignedStudents'],
    '/tutor/student'            => ['App\Controller\TutorController', 'viewStudent'],

    // Profil
    '/profile'                  => ['App\Controller\ProfileController', 'index'],
    '/profile/submit'           => ['App\Controller\ProfileController', 'submit'],

    // Admin
    '/admin/dashboard'          => ['App\Controller\AdminController', 'dashboard'],
    '/admin/stats'              => ['App\Controller\AdminController', 'stats'],
    '/admin/tab/users'          => ['App\Controller\AdminController', 'tabUsers'],
    '/admin/tab/requests'       => ['App\Controller\AdminController', 'tabRequests'],
    '/admin/tab/companies'      => ['App\Controller\AdminController', 'tabCompanies'],
    '/admin/users/updateRole'   => ['App\Controller\AdminController', 'updateUserRole'],
    '/admin/users/delete'       => ['App\Controller\AdminController', 'deleteUser'],
    '/admin/users/suspend'      => ['App\Controller\AdminController', 'toggleActive'],
    '/admin/requests/view'      => ['App\Controller\AdminController', 'viewRequest'],
    '/admin/companies/delete'   => ['App\Controller\AdminController', 'deleteCompany'],
    '/admin/companies/view'     => ['App\Controller\AdminController', 'viewCompany'],
    '/admin/companies/requests' => ['App\Controller\AdminController', 'getCompanyRequests'],

    // Responsable pédagogique
    '/responsable/requestList'      => ['App\Controller\ResponsablePedaController', 'listeDemandes'],
    '/responsable/detailRequest'    => ['App\Controller\ResponsablePedaController', 'detailDemande'],
    '/responsable/traiter'          => ['App\Controller\ResponsablePedaController', 'traiter'],

    // Secrétariat
    '/secretary/dashboard'               => ['App\Controller\SecretaryController', 'dashboard'],
    '/secretary/details'                => ['App\Controller\SecretaryController', 'detailsFile'],
    '/secretary/update-document-status' => ['App\Controller\SecretaryController', 'updateDocumentStatus'],
    '/secretary/validate-all-documents' => ['App\Controller\SecretaryController', 'validateAllDocuments'],
    '/secretary/save-comment'           => ['App\Controller\SecretaryController', 'saveComment'],

    // Mot de passe oublié
    '/forgot-password'         => ['App\Controller\AuthController', 'showForgotForm'],
    '/forgot-password/post'    => ['App\Controller\AuthController', 'sendResetLink'],
    '/reset-password'          => ['App\Controller\AuthController', 'showResetForm'],
    '/reset-password/post'     => ['App\Controller\AuthController', 'resetPassword'],
];