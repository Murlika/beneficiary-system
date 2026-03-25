<?php
namespace App\Libraries;

class AiAssistant {
    // Тут будет логика запроса к OpenAI или локальной LLM
    public function parseSearchQuery(string $rawText): array {
        // Пример: "покажи консультации за вчера" 
        // Превращаем в массив для нашей модели: ['type' => 'consult', 'date' => '2026-03-23']
        return [
            'original' => $rawText,
            'filters'  => [] // Сюда ИИ положит распарсенные данные
        ];
    }
}
?>