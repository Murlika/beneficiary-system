<?php
namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class LogController extends ResourceController
{
    protected $format = 'json';

    public function clientLog()
    {
        $json = $this->request->getJSON(true);

        if (empty($json) || !is_array($json)) {
            return $this->fail('Invalid JSON format, bro!', 400);
        }

        $level   = $json['level'] ?? 'error'; // Если фронт приуныл, обычно это ошибка
        $tag     = $json['tag']   ?? 'FRONT'; // Тег компонента (таблица, ии и т.д.)
        $behavior = $json['behavior'] ?? 'FRONTEND';
        $message = $json['message'] ?? 'Безмолвный крик динозавра...';
        $context = $json['context'] ?? [];

        dino_log($level, $tag, $behavior, $message, $context);

        return $this->respond(['status' => 'success', 'received' => true], 200);
    }
}