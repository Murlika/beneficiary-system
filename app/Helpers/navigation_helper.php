<?php

/**
 * Определяет текущую страницу для Angular и навигации
 */
if (!function_exists('get_current_angular_page')) {
    function get_current_angular_page(): string {
        $uri = service('uri');
        
        // Получаем первый сегмент (после домена)
        $segment = $uri->getSegment(1);

        // Сопоставляем URL с ключами наших "островов"
        return match($segment) {
            'import'          => 'import',
            'beneficiaries'   => 'beneficiaries',
            'dashboard', ''   => 'services',
            default           => 'default'
        };
    }
}

/**
 * Проверяет, является ли страница активной (для классов CSS)
 */
if (!function_exists('is_page_active')) {
    function is_page_active(string $pageName, string $activeClass = 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/50'): string {
        return get_current_angular_page() === $pageName ? $activeClass : 'text-indigo-200';
    }
}
