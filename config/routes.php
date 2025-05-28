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
    '/logout' => ['App\Controller\LogoutController', 'index'],
    '/mentions-legales' => ['App\Controller\AuthController', 'mentionsLegales'],

    // Student
    '/student/new-request'      => ['App\Controller\StudentController', 'newRequest'],
    '/student/request/step2'    => ['App\Controller\StudentController', 'step2'],
    '/student/request/step3'    => ['App\Controller\StudentController', 'step3'],
    '/student/request/step4'    => ['App\Controller\StudentController', 'step4'],
    '/student/request/step5'    => ['App\Controller\StudentController', 'step5'],
    '/student/request/view'     => ['App\Controller\StudentController', 'viewRequest'],
    '/student/request/submit'   => ['App\Controller\StudentController', 'submitRequest'],
    '/student/upload-correction' => ['App\Controller\StudentController', 'uploadCorrection'],

    // Tuteur
    '/tutor/dashboard'          => ['App\Controller\TutorController', 'index'],
    '/tutor/update'             => ['App\Controller\TutorController', 'updateCapacity'],
    '/tutor/students'           => ['App\Controller\TutorController', 'assignedStudents'],
    '/tutor/student'            => ['App\Controller\TutorController', 'viewStudent'],

    // Profil
    '/profile'                  => ['App\Controller\ProfileController', 'index'],
    '/profile/submit'           => ['App\Controller\ProfileController', 'submit'],

    //  Ajout du dashboard admin
    '/admin/dashboard' => ['App\Controller\AdminController', 'dashboard'],
    '/admin/stats' => ['App\Controller\AdminController', 'stats'],
    '/admin/tab/users'      => ['App\Controller\AdminController', 'tabUsers'],
    '/admin/tab/requests'   => ['App\Controller\AdminController', 'tabRequests'],
    '/admin/tab/companies'  => ['App\Controller\AdminController', 'tabCompanies'],
    '/admin/users/updateRole' => ['App\Controller\AdminController', 'updateUserRole'],
    '/admin/users/delete' => ['App\Controller\AdminController', 'deleteUser'],
    '/admin/users/suspend' => ['App\Controller\AdminController', 'toggleActive'],
    '/admin/requests/view' => ['App\Controller\AdminController', 'viewRequest'],
    '/admin/requests/updateTutor' => ['App\Controller\AdminController', 'updateTutor'],
    '/admin/companies/delete' => ['App\Controller\AdminController', 'deleteCompany'],
    '/admin/companies/view' => ['App\Controller\AdminController', 'viewCompany'],
    '/admin/companies/requests' => ['App\Controller\AdminController', 'getCompanyRequests'],

    // Responsable pédagogique
    '/responsable/requestList'      => ['App\Controller\ResponsablePedaController', 'listeDemandes'],
    '/responsable/detailRequest'    => ['App\Controller\ResponsablePedaController', 'detailDemande'],
    '/responsable/traiter'          => ['App\Controller\ResponsablePedaController', 'traiter'],
    '/responsable/switchTutor' => ['\App\Controller\ResponsablePedaController', 'switchTutor'],

    //Secretary
    '/secretary/dashboard' => ['App\Controller\SecretaryController', 'dashboard'],
    '/secretary/details' => ['App\Controller\SecretaryController', 'detailsFile'],
    '/secretary/update-document-status' => ['App\Controller\SecretaryController', 'updateDocumentStatus'],
    '/secretary/validate-all-documents' => ['App\Controller\SecretaryController', 'validateAllDocuments'],
    '/secretary/save-comment' => ['App\Controller\SecretaryController', 'saveComment'],

    '/cfa/dashboard' => ['App\Controller\CFAController', 'dashboard'],
    '/cfa/validate' => ['App\Controller\CFAController', 'validate'],
    '/document/zip' => ['App\Controller\DocumentController', 'zip'],

    '/direction/dashboard' => ['App\Controller\DirectionController', 'dashboard'],
    '/direction/request' => ['App\Controller\DirectionController', 'validateView'],
    '/direction/view'     => ['App\Controller\DirectionController', 'validateView'],
    '/direction/validate' => ['App\Controller\DirectionController', 'validate'],
    '/document/zip-by-request' => ['App\Controller\DocumentController', 'zipByRequest'],
    '/direction/upload-signed' => ['App\Controller\DirectionController', 'uploadSigned'],
    '/document/summary' => ['App\Controller\DocumentController', 'viewSummaryByRequest'],



    // pour la signature entreprise
    '/signature/{token}'        => ['App\Controller\SignController', 'show'],
    '/signature/process'        => ['App\Controller\SignController', 'process'],
    '/signature/send/{id}'      => ['App\Controller\SignController', 'send'],
    '/signature/status/{id}'    => ['App\Controller\SignController', 'status'],
    '/signature/download/{id}'  => ['App\Controller\SignController', 'download'],
    '/responsable/generer-lien-entreprise' => ['App\Controller\ResponsablePedaController', 'genererLienSignatureEntreprise'],
    
    '/signature/convention' => ['App\Controller\SignController', 'afficherFormulaire'],
   '/signature/convention/valider' => ['App\Controller\SignController', 'enregistrerSignature'],






];
