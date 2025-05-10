<?php
declare(strict_types=1);

namespace App\Controller;

use Core\Controller;

class DashboardController extends Controller
{
    public function index(): void
    {
        echo '<h1>Dashboard</h1><p>Vous êtes connecté !</p>';
    }
}
