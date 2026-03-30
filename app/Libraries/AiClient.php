<?php
// app/Libraries/AiClient.php
namespace App\Libraries;

class AiClient {
private $lastUsage = [];

public function sendRequest(array $messages): string {
    $client = \Config\Services::curlrequest();
    
    try {
        $response = $client->post('https://openrouter.ai', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENROUTER_KEY'),
                'Content-Type'  => 'application/json',
                'HTTP-Referer'  => 'http://localhost',
                'X-Title'       => 'BSR Project'
            ],
            'json' => [
                'model'  => 'google/gemini-2.0-flash-001',
                'stream' => false,
                'messages' => $messages,
                'response_format' => ['type' => 'json_object'] 
            ],
            'timeout' => 30
        ]);

        $data = json_decode($response->getBody(), true);
        
        $content = $data['choices'][0]['message']['content'] ?? '';
        $this->lastUsage = $data['usage'] ?? ['total_tokens' => 0];

        error_log("[AiClient][INFO] Запрос выполнен успешно. Токены: " . ($this->lastUsage['total_tokens'] ?? 0));

        return $this->cleanAiResponse($content);

    } catch (\Exception $e) {
        // Сбой при запросе
        error_log("[AiClient][ERROR] Сбой при отправке запроса: " . $e->getMessage());
        throw $e;
    }
}

private function cleanAiResponse(string $content): string {
    // 1. Убираем маркдаун-теги (json ```)
    $content = preg_replace('/```(?:json)?|```/', '', $content);
    $content = trim($content);

    // 2. Ищем ПЕРВУЮ открывающую скобку (любую: { или [)
    $firstBrace = strpos($content, '{');
    $firstBracket = strpos($content, '[');
    
    // Определяем, что идет раньше
    if ($firstBrace === false) $start = $firstBracket;
    elseif ($firstBracket === false) $start = $firstBrace;
    else $start = min($firstBrace, $firstBracket);

    // 3. Ищем ПОСЛЕДНЮЮ закрывающую скобку (} или ])
    $lastBrace = strrpos($content, '}');
    $lastBracket = strrpos($content, ']');
    
    if ($lastBrace === false) $end = $lastBracket;
    elseif ($lastBracket === false) $end = $lastBrace;
    else $end = max($lastBrace, $lastBracket);

    if ($start !== false && $end !== false) {
        return substr($content, $start, $end - $start + 1);
    }

    return $content;
}


public function getRawAnswer(array $messages): string {
    $client = \Config\Services::curlrequest();
    
    try {    
        $response = $client->post('https://openrouter.ai/api/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENROUTER_KEY'),
                'HTTP-Referer'  => 'http://localhost',
            ],
            'json' => [
                'model'  => 'google/gemini-2.0-flash-001',
                'messages' => $messages,
                'stream' => false
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        $this->lastUsage = $data['usage'] ?? ['total_tokens' => 0];

        return $data['choices'][0]['message']['content'] ?? 'Бро, я чет приуныл и не ответил...';
    } catch (\Exception $e) {
        // Сбой при запросе
        error_log("[AiClient][ERROR] Сбой при отправке запроса: " . $e->getMessage());
        throw $e;
    }        
}

public function translateToFilters(string $prompt): array {

    $raw = $this->getRawAnswer([['role' => 'user', 'content' => $prompt]]);

    return json_decode($this->cleanAiResponse($raw), true) ?? [];
}

public function getLastUsage(): array {
    return $this->lastUsage;
}

}
