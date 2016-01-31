<?php

if (!function_exists('cleanArray')) {
    /**
     * Очистка и обеззаражевание массива
     *
     * @param array $array      Исходный массив
     * @param bool  $stripTags  Удалять или нет теги
     * @param bool  $unsetEmpty Удалять или нет пустые ключи
     * @param bool  $unique     Оставлять только уникальные значения
     *
     * @return void
     */
    function cleanArray(array &$array, $stripTags = true, $unsetEmpty = true, $unique = false)
    {
        foreach ($array as $k => &$e) {
            if (is_array($e)) {
                cleanArray($e, $stripTags, $unsetEmpty);
                continue;
            }

            if ($stripTags) {
                $e = strip_tags($e);
            }

            $e = entities(trim($e));

            if ($unsetEmpty and $e == '') {
                unset($array[$k]);
            }

            if ($unique) {
                $array = array_unique($array);
            }
        }
    }
}

if (!function_exists('entities')) {
    /**
     * Кодирует символы в HTML сущности
     *
     * @param string $string
     *
     * @return string
     */
    function entities($string)
    {
        return htmlentities($string, ENT_QUOTES, 'UTF-8', false);
    }
}

if (!function_exists('e')) {
    /**
     * @see entities
     */
    function e($string)
    {
        return entities($string);
    }
}

if (!function_exists('isAjax')) {
    /**
     * Проверяет AJAX запрос или нет
     *
     * @return bool
     */
    function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
               && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}

if (!function_exists('isMethod')) {
    /**
     * Проверяет каким методом пришел запрос
     *
     * @param string $method
     *
     * @return bool
     */
    function isMethod($method = 'get')
    {
        return strtolower($_SERVER['REQUEST_METHOD']) === strtolower($method);
    }
}
