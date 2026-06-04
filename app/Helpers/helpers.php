<?php
// app/Helpers/helpers.php

if (!function_exists('getProductIcon')) {
    /**
     * Връща емоджи икона за продукт на база името му
     * 
     * @param string $name
     * @return string
     */
    function getProductIcon($name)
    {
        $icons = [
            // Храни и напитки
            'хляб' => '🍞',
            'хлеб' => '🍞',
            'пита' => '🥙',
            'мляко' => '🥛',
            'сирене' => '🧀',
            'кашкавал' => '🧀',
            'масло' => '🧈',
            'кисело мляко' => '🥛',
            'яйца' => '🥚',
            'яйце' => '🥚',
            
            // Месни продукти
            'месо' => '🍖',
            'говеждо' => '🥩',
            'свинско' => '🍖',
            'пилешко' => '🍗',
            'телешко' => '🥩',
            'агнешко' => '🍖',
            'кюфте' => '🍔',
            'кебапче' => '🍔',
            'наденица' => '🌭',
            'луканка' => '🌭',
            
            // Риба и морски дарове
            'риба' => '🐟',
            'сьомга' => '🐟',
            'паламуд' => '🐟',
            'калмар' => '🦑',
            'миди' => '🦪',
            'скариди' => '🦐',
            
            // Зеленчуци
            'зеленчук' => '🥬',
            'домати' => '🍅',
            'домат' => '🍅',
            'краставица' => '🥒',
            'пипер' => '🫑',
            'чушка' => '🫑',
            'морков' => '🥕',
            'картоф' => '🥔',
            'картофи' => '🥔',
            'лук' => '🧅',
            'чесън' => '🧄',
            'спанак' => '🥬',
            'броколи' => '🥦',
            'карфиол' => '🥦',
            'тиквичка' => '🥒',
            'патладжан' => '🍆',
            
            // Плодове
            'плод' => '🍎',
            'ябълка' => '🍎',
            'ябълки' => '🍎',
            'круша' => '🍐',
            'банан' => '🍌',
            'портокал' => '🍊',
            'мандарина' => '🍊',
            'лимон' => '🍋',
            'грозде' => '🍇',
            'ягода' => '🍓',
            'ягоди' => '🍓',
            'малина' => '🫐',
            'боровинка' => '🫐',
            'праскова' => '🍑',
            'кайсия' => '🍑',
            'череша' => '🍒',
            'вишна' => '🍒',
            'диня' => '🍉',
            'пъпеш' => '🍈',
            
            // Пица и паста
            'пица' => '🍕',
            'спагети' => '🍝',
            'паста' => '🍝',
            'лазаня' => '🍝',
            'ориз' => '🍚',
            
            // Десерти
            'десерт' => '🍰',
            'торта' => '🎂',
            'сладолед' => '🍦',
            'шоколад' => '🍫',
            'бисквитка' => '🍪',
            'вафла' => '🧇',
            'мед' => '🍯',
            'сладко' => '🍯',
            
            // Супи и салати
            'супа' => '🥣',
            'салата' => '🥗',
            'шопска салата' => '🥗',
            'овчарска салата' => '🥗',
            
            // Напитки
            'вода' => '💧',
            'минерална вода' => '💧',
            'сок' => '🥤',
            'бира' => '🍺',
            'вино' => '🍷',
            'ракия' => '🥃',
            'уиски' => '🥃',
            'водка' => '🥃',
            'кафе' => '☕',
            'еспресо' => '☕',
            'чай' => '🍵',
            'кола' => '🥤',
            'фанта' => '🥤',
            
            // Дрехи и обувки (за мола)
            'дреха' => '👕',
            'тениска' => '👕',
            'риза' => '👔',
            'панталон' => '👖',
            'дънки' => '👖',
            'рокля' => '👗',
            'пола' => '👗',
            'яке' => '🧥',
            'палто' => '🧥',
            'обувки' => '👟',
            'маратонки' => '👟',
            'токчета' => '👠',
            'часовник' => '⌚',
            'бижу' => '💍',
            'гривна' => '💍',
            'огърлица' => '💍',
            
            // Електроника
            'телефон' => '📱',
            'таблет' => '📱',
            'лаптоп' => '💻',
            'компютър' => '💻',
            'телевизор' => '📺',
            'слушалки' => '🎧',
            
            // Козметика
            'парфюм' => '🧴',
            'козметика' => '💄',
            'червило' => '💄',
            'спирала' => '💄',
            
            // Книги и канцеларски материали
            'книга' => '📚',
            'тетрадка' => '📓',
            'писалка' => '✒️',
            'химикал' => '✒️',
            
            // Играчки
            'играчка' => '🧸',
            'кукла' => '🎎',
            'количка' => '🚗',
        ];
        
        $nameLower = strtolower($name);
        
        foreach ($icons as $keyword => $icon) {
            if (str_contains($nameLower, $keyword)) {
                return $icon;
            }
        }
        
        // Подразбираща се икона
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