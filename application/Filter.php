<?php

namespace application;

class Filter
{
    // --- Métodos de obtención por fuente ---

    //obtiene datos via POST, quita espacios al inicio y al final
    public static function getPost($key)
    {
        return isset($_POST[$key]) ? self::sanitizeString($_POST[$key]) : '';
    }

    //obtiene datos via POST, sanitiza numeros enteros, devolviendo 0 si no existe
    public static function getPostInt($key)
    {
        return isset($_POST[$key]) ? self::sanitizeInt($_POST[$key]) : 0;
    }

    // obtiene datos via POST, sanitiza números decimales (float), devolviendo 0.0 si no existe o no es válido
    public static function getPostFloat($key)
    {
        return isset($_POST[$key]) ? self::sanitizeFloat($_POST[$key]) : 0.0;
    }

    //obtiene datos via POST, elimina todos los espacios, menos letras, numeros y guion bajo
    public static function getPostAlphaNum($key)
    {
        return isset($_POST[$key]) ? self::sanitizeAlphaNum($_POST[$key]) : '';
    }

    public static function getPostHtml($key)
    {
        // Este método devuelve el contenido TAL CUAL lo envió el usuario
        return isset($_POST[$key]) ? trim($_POST[$key]) : '';
    }

    public static function getPostRaw($key)
    {
        return isset($_POST[$key]) ? htmlspecialchars(trim($_POST[$key]), ENT_QUOTES, 'UTF-8') : '';
    }
    //Obtiene datos via GET y funciona semejante a getPost()
    public static function getQuery($key)
    {
        return isset($_GET[$key]) ? self::sanitizeString($_GET[$key]) : '';
    }

    //obtiene datos via GET, funciona semejante a getPostInt()
    public static function getQueryInt($key)
    {
        return isset($_GET[$key]) ? self::sanitizeInt($_GET[$key]) : 0;
    }

    // obtiene datos via GET, sanitiza números decimales (float), devolviendo 0.0 si no existe o no es válido
    public static function getQueryFloat($key)
    {
        return isset($_GET[$key]) ? self::sanitizeFloat($_GET[$key]) : 0.0;
    }

    //obtiene datos via GET, semejante a getPostAlphaNum
    public static function getQueryAlphaNum($key)
    {
        return isset($_GET[$key]) ? self::sanitizeAlphaNum($_GET[$key]) : '';
    }

    // --- Métodos de sanitización internos ---
    //Quita espacios al inicio y al final
    //Escapa caracteres especiales HTML (evita inyección XSS).
    //NO elimina espacios entre palabras.
    //Campos de texto libres (nombre, descripción, comentarios).
    public static function sanitizeString($string)
    {
        return filter_var(trim($string), FILTER_SANITIZE_SPECIAL_CHARS);
    }

    //Valida si el valor es un entero válido.
    //Si no, devuelve 0.
    //Campos numéricos (edad, id, cantidad).
    public static function sanitizeInt($int)
    {
        return filter_var($int, FILTER_VALIDATE_INT) !== false ? (int)$int : 0;
    }

    public static function sanitizeFloat($float)
    {
        // Normaliza coma decimal por punto y quita espacios
        $value = str_replace(',', '.', trim((string)$float));

        // Sanea permitiendo fracción y notación científica
        $sanitized = filter_var(
            $value,
            FILTER_SANITIZE_NUMBER_FLOAT,
            FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_SCIENTIFIC
        );

        // Validación final
        return is_numeric($sanitized) ? (float)$sanitized : 0.0;
    }

    // Quita espacios y cualquier carácter que NO sea letra, número o guion bajo.
    // Esto elimina también espacios internos y otros símbolos.
    // Campos que deben contener solo letras y números (usuario, código, slug).

    // NO usar para descripciones o textos con espacios.
    public static function sanitizeAlphaNum($string)
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', trim($string));
    }

    public static function sanitizeSlug($string)
    {
        // Limpia espacios, convierte a minúsculas y solo permite a-z, 0-9 y guiones
        $string = trim(strtolower($string));
        return preg_replace('/[^a-z0-9-]/', '', $string);
    }
}

