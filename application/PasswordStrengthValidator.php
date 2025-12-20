<?php
namespace application;

class PasswordStrengthValidator
{
    public static function isValid(string $password): bool
    {
        // Reglas mínimas: al menos 8 caracteres, una mayúscula, un número
        return preg_match('/^(?=.*[A-Z])(?=.*\d).{8,}$/', $password);
    }
}
