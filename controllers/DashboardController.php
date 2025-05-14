<?php
namespace App\Controller;

use App\View;
use App\BaseController;

class DashboardController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();
        View::render('dashboard/student');
    }
}
