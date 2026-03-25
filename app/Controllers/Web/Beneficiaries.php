<?php
namespace App\Controllers\Web;
use App\Controllers\BaseController;

class Beneficiaries extends BaseController {
    public function index() {
        return view('web/beneficiaries', ['title' => 'Благополучатели']);
    }
}