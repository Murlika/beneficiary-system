<?php

/**
 * 🦕 Dino System Helper
 * Утилиты для работы с серверными лимитами и форматами
 */

if (! function_exists('dino_parse_size')) {
    /**
     * Конвертирует строку лимита PHP (2M, 1G) в байты
     */
    function dino_parse_size(string $size): int
    {
        $unit = strtolower(substr($size, -1));
        $val  = (int)$size;

        return match($unit) {
            'g' => $val * 1024 * 1024 * 1024,
            'm' => $val * 1024 * 1024,
            'k' => $val * 1024,
            default => $val,
        };
    }
}
