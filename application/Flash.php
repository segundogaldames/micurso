<?php

namespace application;

class Flash
{
    public static function success(string $message): void
    {
        $_SESSION['flash']['success'] = $message;
    }

    public static function error(string $message): void
    {
        $_SESSION['flash']['error'] = $message;
    }

    public static function warning(string $message): void
    {
        $_SESSION['flash']['warning'] = $message;
    }

    public static function info(string $message): void
    {
        $_SESSION['flash']['info'] = $message;
    }

    public static function get(): array
    {
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']); // Elimina los mensajes después de usarlos (una sola vez)
        return $messages;
    }
}
