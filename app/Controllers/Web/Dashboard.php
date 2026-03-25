<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    /**
     * Отображает главную страницу реестра (Рыба + Angular)
     */
    public function index()
    {
        return view('web/dashboard');
    }
}
