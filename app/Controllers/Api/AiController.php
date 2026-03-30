<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Services\AiService;

class AiController extends ResourceController
{   
    protected $aiService;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        
        // Теперь это безопасно и по фен-шую 💻
        $this->aiService = service('ai');
    }

    public function ask() {
        $userText = $this->request->getPost('query') ?? 'Кто сегодня молодец?';

        dino_log('info', 'AI', 'USER_PROMPT', "Юзер чешет ИИ за ушком: " . substr($userText, 0, 100) . "...", [
            'full_prompt' => $userText
        ]);

        try {
            $result = $this->aiService->parseUserIntent($userText);

            dino_log('info', 'AI', 'SUCCESS', "ИИ успешно в ответ", [
                'response_length' => strlen($userText),
                'tokens_used' => $this->aiService->getLastTokenCount() 
            ]);

            return $this->respond([
                'status' => 'success',
                'data'   => $result,
                'raw_query' => $userText
            ]);
        } catch (\Exception $e) {
            // ☄️ Метеорит упал на ИИ
            dino_log('error', 'AI', 'CRASH', "ИИ потерял сознание: " . $e->getMessage());
            return $this->fail('ИИ временно вымер... 🌋');
        }

    }

public function broChat()
{
    $userQuestion = $this->request->getVar('q') ?? "Кто сегодня молодец?";
   //throw new \Exception("Синтаксическая ошибка мезозоя! 🦴");

    dino_log('info', 'AI', 'USER_PROMPT', "Юзер хочет общения с ИИ: " . substr($userQuestion, 0, 100) . "...", [
            'full_prompt' => $userQuestion
        ]);

   try {
        $answer = $this->aiService->getRawAnswer($userQuestion);

        dino_log('info', 'AI', 'SUCCESS', "ИИ успешно в ответ", [
            'response_length' => strlen($answer),
            'tokens_used' => $this->aiService->getLastTokenCount() 
        ]);

        return $this->respond([
            'question' => $userQuestion,
            'answer'   => $answer,
            'status'   => 'bro_mode_on'
        ]);
    } catch (\Exception $e) {
        // ☄️ Метеорит упал на ИИ
        dino_log('error', 'AI', 'CRASH', "ИИ потерял сознание: " . $e->getMessage());
        return $this->fail('ИИ временно вымер... 🌋');
    }
}

public function aiSearch() {
    $rawText = $this->request->getVar('q');

    dino_log('info', 'AI', 'USER_PROMPT', "Юзер запрашивает фильтры у ИИ: " . substr($rawText, 0, 100) . "...", [
            'full_prompt' => $rawText
        ]);

    try {
    $filters = $this->aiService->translateToFilters($rawText);

    $filtersJson = json_encode($filters, JSON_UNESCAPED_UNICODE);

    dino_log('info', 'AI', 'SUCCESS', "ИИ успешно в ответ", [
        'response_length' => strlen($filtersJson),
        'tokens_used' => $this->aiService->getLastTokenCount(),
        'preview'         => substr($filtersJson, 0, 100)
    ]);

    return $this->respond([
        'filters' => $filters, // Для дебага, чтобы видеть, как ИИ понял запрос
        'diplodoc' => "Распознал " . count($filters) . " фильтров по твоему запросу! 🦕"
    ]);
    } catch (\Exception $e) {
        // ☄️ Метеорит упал на ИИ
        dino_log('error', 'AI', 'CRASH', "ИИ потерял сознание: " . $e->getMessage());
        return $this->fail('ИИ временно вымер... 🌋');
    }

}

}