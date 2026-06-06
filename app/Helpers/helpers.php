<?php
// app/Helpers/helpers.php

if (!function_exists('getProductIcon')) {
    /**
     * Връща подходяща емоджи икона за продукт
     */
    function getProductIcon(string $name): string
    {
        $nameLower = mb_strtolower(trim($name));

        // === Основен mapping (keywords с приоритет) ===
        $iconMap = [
            // Зеленчуци
            'домат' => '🍅',
            'краставиц' => '🥒',
            'пипер' => '🫑',
            'чушк' => '🫑',
            'картоф' => '🥔',
            'морков' => '🥕',
            'зеле' => '🥬',
            'маруля' => '🥬',
            'лук' => '🧅',
            'чесън' => '🧄',

            // Плодове
            'ябълк' => '🍎',
            'круша' => '🍐',
            'банан' => '🍌',
            'портокал' => '🍊',
            'мандарин' => '🍊',
            'грозд' => '🍇',
            'ягод' => '🍓',
            'череш' => '🍒',
            'киви' => '🥝',

            // Месо и риба
            'пилешк' => '🍗',
            'пиле' => '🍗',
            'говежд' => '🥩',
            'свинск' => '🥩',
            'агнешк' => '🥩',
            'месо' => '🍖',
            'риба' => '🐟',
            'салам' => '🌭',
            'колбас' => '🌭',

            // Млечни
            'сирен' => '🧀',
            'кашкавал' => '🧀',
            'мляко' => '🥛',
            'йогурт' => '🥛',
            'масло' => '🧈',

            // Хляб и тестени
            'хляб' => '🍞',
            'питк' => '🍞',
            'багет' => '🥖',
            'паста' => '🍝',
            'пица' => '🍕',
            'ориз' => '🍚',

            // Напитки
            'кафе' => '☕',
            'чай' => '🍵',
            'сок' => '🥤',
            'бира' => '🍺',
            'вино' => '🍷',
            'вода' => '💧',
            'кока' => '🥤',
            'кола' => '🥤',

            // Десерти
            'десерт' => '🍰',
            'торт' => '🍰',
            'шоколад' => '🍫',
            'сладолед' => '🍦',
        ];

        // 1. Точно съвпадение
        foreach ($iconMap as $keyword => $icon) {
            if ($nameLower === $keyword || $nameLower === mb_strtolower($keyword)) {
                return $icon;
            }
        }

        // 2. Частично съвпадение (по приоритет)
        foreach ($iconMap as $keyword => $icon) {
            if (mb_strpos($nameLower, $keyword) !== false) {
                return $icon;
            }
        }

        // 3. Специфични допълнителни правила
        if (strpos($nameLower, 'зелен') !== false || strpos($nameLower, 'салат') !== false) {
            return '🥗';
        }
        if (strpos($nameLower, 'супа') !== false) {
            return '🥣';
        }

        // Fallback
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