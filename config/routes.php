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

    // Admin
    '/admin/dashboard' => ['App\Controller\AdminController', 'dashboard'],
    '/admin/tab/users'      => ['App\Controller\AdminController', 'tabUsers'],
    '/admin/tab/requests'   => ['App\Controller\AdminController', 'tabRequests'],
    '/admin/tab/companies'  => ['App\Controller\AdminController', 'tabCompanies'],
// Routes Direction - Version Adaptée
'/direction/dashboard' => ['App\Controller\DirectionController', 'dashboard'], // Liste des demandes
'/direction/details' => ['App\Controller\DirectionController', 'detailsFile'], // Détail demande (avec id GET param)

// Routes pour les actions sur les documents (NOUVELLES)
'/direction/document/sign' => ['App\Controller\DirectionController', 'signerDocument'], // Signer un document (POST)
'/direction/document/validate' => ['App\Controller\DirectionController', 'validerDocument'], // Valider un document (POST)
'/direction/document/comment' => ['App\Controller\DirectionController', 'updateCommentaire'], // Mettre à jour commentaire (POST)

// Routes pour les actions globales (NOUVELLES)
'/direction/documents/sign-all' => ['App\Controller\DirectionController', 'signerTousDocuments'], // Signer tous les documents (POST)
'/direction/documents/validate-all' => ['App\Controller\DirectionController', 'validerTousDocuments'], // Valider tous les documents (POST)
'/direction/dossier/finalize' => ['App\Controller\DirectionController', 'finaliserDossier'], // Finaliser le dossier (POST)

// Route ancienne conservée pour compatibilité
'/direction/demande/sign' => ['App\Controller\DirectionController', 'signerDemande'], // Signature demande (POST) - À garder si utilisée ailleurs
'/direction/save-comment'           => ['App\Controller\DirectionController', 'saveComment'],


];
