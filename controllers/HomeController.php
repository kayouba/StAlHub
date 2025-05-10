<?php
namespace App\Controller;

use Core\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
        // Passe un titre à la vue
        $this->render('home', [
            'title' => 'Bienvenue sur StalHub',
        ]);
    }
}