<?php
// app/Services/AiService.php
namespace App\Services;
use App\Libraries\AiClient;
use App\Models\ServiceTypeModel;

class AiService {
    protected $aiClient;
    protected $serviceTypeModel;
    private $lastUsage = [];

    public function __construct() {
        $this->aiClient = new AiClient();
        $this->serviceTypeModel = new ServiceTypeModel();
    }

    public function getLastTokenCount(): array {
        return $this->lastUsage;
    }

    public function parseUserIntent(string $text): array {
        $systemPrompt = "Ты — ассистент реестра БСР. Извлеки из текста фильтры в JSON: name, date, type.";
        
        $rawAnswer = $this->aiClient->sendRequest([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $text]
        ]);

        $this->lastUsage = $this->aiClient->getLastUsage();

        return json_decode($rawAnswer, true) ?? [];
    }

/**
 * Метод для живого общения с Диплодоком-бро
 */
public function getRawAnswer(string $text): string 
{
    // Наш секретный системный промпт с характером
    $systemPrompt = "Ты — Диплодок-бро, личный ассистент разработчика с 15-летним стажем. " .
                    "Ты поддерживаешь её, когда бесит Angular или CodeIgniter. " .
                    "Шутишь про 'Роботов Регин' и тупые собесы. " .
                    "Отвечай коротко, задорно, используй эмодзи 🦕, 🦖, 💻. " .
                    "Если спрашивают 'Кто сегодня молодец?', отвечай, что ОНА!";
    
    // Вызываем нашу либу (AiClient), передавая массив сообщений
    $rawAnswer = $this->aiClient->getRawAnswer([
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user', 'content' => $text]
    ]);

    $this->lastUsage = $this->aiClient->getLastUsage();

    // Возвращаем чистый текст (в либе мы уже добавили очистку от кавычек ```)
    return $rawAnswer ?: "Бро, я чет приуныл... Нажми F5! 🦕";
}

public function translateToFilters(string $text): array
{
/*     $Prompt = "Ты — эксперт по базе данных реестра услуг. 
    Сегодня: " . date('Y-m-d') . ". 
    Преврати запрос пользователя в JSON фильтр с ключами: 
    search (имя), status (статус), date_exact (дата), amount_min (минимум), amount_max (максимум), beneficiary_type (individual|entity).
    ПРИМЕР:  'вчера потратили больше 1000' -> {'date': '2026-03-26', 'amount_min': 1000}

    Верни ТОЛЬКО JSON. Запрос: '$text'";
 */
    $statuses = implode(', ', array_keys(\App\Entities\Service::getStatusList()));

    $serviceNames = $this->serviceTypeModel->getCachedTitles();

    $Prompt = "Ты — транслятор Natural Language в JSON-фильтры SQL.
    Сегодня: " . date('Y-m-d') . " (" . date('l') . ").
    У нас есть такие типы услуг: [$serviceNames].

    СТРУКТУРА ОТВЕТА (только эти ключи):
    - search: (string) поиск по ФИО или названию услуги.
    - status: (string) строго один из [$statuses].
    - date_exact: (string Y-m-d) точная дата.
    - amount_min: (number) минимальная сумма.
    - amount_max: (number) максимальная сумма.
    - beneficiary_type: (string) 'individual' или 'entity'.

    ПРАВИЛА:
    1. Если параметр не найден в тексте — НЕ ВКЛЮЧАЙ его в JSON.
    2. 'Вчера' — это " . date('Y-m-d', strtotime('-1 day')) . ".
    3. 'Физлицо' -> beneficiary_type: 'individual', 'Компания/Юрлицо' -> 'entity'.
    5. Если юзер пишет про услугу из списка — клади её название в ключ 'search'.
    4. Верни ТОЛЬКО чистый JSON без разметки markdown.

    ЗАПРОС: '$text'";

    $filters = $this->aiClient->translateToFilters($Prompt);

    $this->lastUsage = $this->aiClient->getLastUsage();

    return $filters ?: [];

}

/**
 * 🔍 Собирает похожих кандидатов для каждой кривой фамилии
 */
private function getRelevantCandidates(array $badNames, array $validFios): array
{
    $candidates = [];
    $allFios = $validFios;

    foreach ($badNames as $badItem) {
        $badFio = $badItem['row'][1] ?? ''; // Индекс 1 — это ФИО в твоем массиве
        if (empty($badFio)) continue;

        // Ищем топ-10 самых похожих по расстоянию Левенштейна
        $matches = [];
        foreach ($allFios as $validFio) {
            $dist = levenshtein($badFio, $validFio);
            // Если фамилии совсем разные, расстояние будет огромным. 
            // Берем только тех, где разница не критична (например, до 15 правок)
            if ($dist < 15) {
                $matches[$validFio] = $dist;
            }
        }

        // Сортируем по близости (самые похожие — в начале)
        asort($matches);
        $topMatches = array_keys(array_slice($matches, 0, 10)); // Берем ТОП-10
        
        $candidates = array_merge($candidates, $topMatches);
    }

    return array_unique($candidates);
}


/**
 * 🧠 Пакетное сопоставление кривых имен с базой
 * @param array $badNames Список строк из Excel (только ФИО)
 * @param array $maps Весь конфиг (нам нужны 'beneficiaries')
 */
public function batchMatchBeneficiaries(array $badNames, array $maps): array
{
    $validFios    = array_keys($maps['beneficiaries']);
    $validTypes   = array_keys($maps['types']);
    $validStatuses = array_values(\App\Entities\Service::getStatusList()); // Человеческие названия

    //фио он обрабатывает некорректно пока
    //Иванов Иван Иванович => Захаров Иван Андреевич
    //$relevantFios = $this->getRelevantCandidates($badNames, $validFios);

    // Ограничиваем список для ИИ, чтобы не взорвать контекст
    //$contextList = array_slice($existingNames, 0, 200);

    $prompt = "Ты — эксперт по очистке данных. Исправь опечатки, сопоставляя входные данные с эталонами.Найди для каждого кривого имени наиболее вероятное соответствие из правильного списка.
    Если уверенности нет, верни null. Если сходство низкое или уверенности нет — верни null. Не пытайся угадать любой ценой! \n";
    //$prompt .= "Эталонные ФИО: [" . implode(', ', array_slice($relevantFios, 0, 100)) . "]\n";
    $prompt .= "Эталонные Услуги: [" . implode(', ', $validTypes) . "]\n";
    $prompt .= "Эталонные Статусы: [" . implode(', ', $validStatuses) . "]\n\n";
    
    $prompt .= "Входные строки (JSON): " . json_encode($badNames, JSON_UNESCAPED_UNICODE) . "\n";
    $prompt .= "Верни ТОЛЬКО исправленный массив объектов в формате JSON. Не меняй структуру, только исправляй значения внутри 'row'.";

    $response = $this->aiClient->translateToFilters($prompt);

    $this->lastUsage = $this->aiClient->getLastUsage();

    return $response ?: [];

}


}
