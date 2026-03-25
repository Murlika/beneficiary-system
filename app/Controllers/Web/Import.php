<?php
namespace App\Controllers\Web;
use App\Controllers\BaseController;

class Import extends BaseController {
    public function index() {
        return view('web/import', ['title' => 'Импорт и Экспорт']);
    }
}
