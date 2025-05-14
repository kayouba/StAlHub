<?php
namespace App\Controller;

use App\View;

class HomeController
{
    public function index(): void
    {
        View::render('auth/login');
    }
}
