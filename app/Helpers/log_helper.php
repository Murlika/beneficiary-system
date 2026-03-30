<?php
if (!function_exists('dino_log')) {
    function dino_log(string $level, string $tag, string $behavior, string $message, array $context = []) {
        $request = \Config\Services::request();
        
        $emoji = match (strtolower($level)) {
            'critical', 'alert' => '☄️', // Полный вымир!
            'error'             => '🌋', // Извержение (баг)
            'warning'           => '⚠️', // Хищник рядом (внимание)
            'info'              => '🥚',      // Жизнь идет своим чередом
            'debug'             => '🐾',     // Просто след на песке
            default             => '🌿',
        };

        $exception = null;
        if (isset($context['exception']) && $context['exception'] instanceof \Throwable) {
            $exception = $context['exception'];
            // Добавляем к сообщению файл и строку, где упал метеорит ☄️
            $message .= " [В файле: {$exception->getFile()} на строке {$exception->getLine()}]";
        }

        $cleanContext = $context;
        unset($cleanContext['exception']); // Стек-трейс не дублируем, он и так выведется
        if (!empty($cleanContext)) {
            $message .= " | Context: " . json_encode($cleanContext, JSON_UNESCAPED_UNICODE);
        }

        // Формируем Trace ID (берем из заголовка фронта)
        $traceId = $request->getHeaderLine('X-Trace-Id') ?: 'no-trace';
        $ip = $request->getIPAddress();
        $env = env('CI_ENVIRONMENT', 'production');
        $version = "1.0.0"; 

        // Наш эталонный шаблон: [env.ver.date][trace][tag][behavior][text][ip]
        $fullMessage = "{$emoji}[{$env}.{$version}][{$traceId}][{$tag}][{$behavior}] {$message} [IP: {$ip}]";

        log_message($level, $fullMessage, $context);
    }
}