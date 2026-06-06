<?php
// app/Helpers/helpers.php

// app/Helpers/helpers.php

if (!function_exists('getProductIcon')) {
    function getProductIcon($name)
    {
        $nameLower = strtolower($name);
        
        // Прости правила за най-честите продукти
        if (str_contains($nameLower, 'домат')) return '🍅';
        if (str_contains($nameLower, 'краставиц')) return '🥒';
        if (str_contains($nameLower, 'пипер')) return '🫑';
        if (str_contains($nameLower, 'пилешк')) return '🍗';
        if (str_contains($nameLower, 'кафе')) return '☕';
        if (str_contains($nameLower, 'хляб')) return '🍞';
        if (str_contains($nameLower, 'сирене')) return '🧀';
        if (str_contains($nameLower, 'месо')) return '🍖';
        if (str_contains($nameLower, 'риба')) return '🐟';
        if (str_contains($nameLower, 'салата')) return '🥗';
        if (str_contains($nameLower, 'супа')) return '🥣';
        if (str_contains($nameLower, 'десерт')) return '🍰';
        if (str_contains($nameLower, 'сок')) return '🥤';
        if (str_contains($nameLower, 'бира')) return '🍺';
        if (str_contains($nameLower, 'вода')) return '💧';
        
        return '📦';
    }
}

if (!function_exists('formatQuantity')) {
    /**
     * Форматира количество според мерната единица
     * 
     * @param float $quantity
     * @param int $decimalPlaces
     * @param string $unit
     * @return string
     */
    function formatQuantity($quantity, $decimalPlaces = 0, $unit = 'бр.')
    {
        $formatted = number_format($quantity, $decimalPlaces);

        if (in_array($unit, ['кг', 'kg', 'л', 'l', 'L', 'м', 'm'])) {
            $formatted = rtrim(rtrim($formatted, '0'), '.');
        }

        return $formatted . ' ' . $unit;
    }
}

if (!function_exists('formatPrice')) {
    /**
     * Форматира цена с валута
     * 
     * @param float $price
     * @param string $currency
     * @return string
     */
    function formatPrice($price, $currency = '€')
    {
        return number_format($price, 2) . ' ' . $currency;
    }
}

if (!function_exists('getStatusBadge')) {
    /**
     * Връща HTML badge за статус
     * 
     * @param string $status
     * @return string
     */
    function getStatusBadge($status)
    {
        $badges = [
            'active' => '<span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">Активен</span>',
            'inactive' => '<span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">Неактивен</span>',
            'pending' => '<span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs">Чакащ</span>',
            'completed' => '<span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">Завършен</span>',
            'cancelled' => '<span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs">Анулиран</span>',
            'paid' => '<span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">Платен</span>',
        ];

        return $badges[$status] ?? '<span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">' . $status . '</span>';
    }
}

if (!function_exists('generateSessionToken')) {
    /**
     * Генерира уникален токен за сесия
     * 
     * @param int $length
     * @return string
     */
    function generateSessionToken($length = 8)
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ0123456789';
        $token = '';
        for ($i = 0; $i < $length; $i++) {
            $token .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $token;
    }
}