<?php

namespace application;

use application\PasswordStrengthValidator;
use Illuminate\Database\Capsule\Manager as Capsule;

class Validator
{
    protected array $data;
    protected array $rules;
    protected array $errors = [];

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    public function validate(): bool
    {
        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $this->data[$field] ?? null;

            $hasRequired = in_array('required', $rules);

            foreach ($rules as $rule) {
                $param = null;
                if (strpos($rule, ':') !== false) {
                    list($rule, $param) = explode(':', $rule);
                }

                // ⚠️ Si el campo está vacío y no es required, salta validaciones
                if (!$hasRequired && ($value === null || $value === '')) {
                    break; // campo opcional vacío, no seguimos validando
                }

                if (!$this->checkRule($field, $value, $rule, $param)) {
                    break; // muestra solo el primer error por campo
                }
            }
        }

        return empty($this->errors);
    }

    protected function checkRule(string $field, $value, string $rule, $param = null): bool
    {
        switch ($rule) {
            case 'required':
                if (is_null($value) || $value === '' || (is_array($value) && empty($value))) {
                    $this->errors[$field] = "El campo $field es obligatorio.";
                    return false;
                }
                break;

            case 'min':
                if (is_string($value) && strlen($value) < (int)$param) {
                    $this->errors[$field] = "El campo $field debe tener al menos $param caracteres.";
                    return false;
                }
                if (is_numeric($value) && $value < (int)$param) {
                    $this->errors[$field] = "El campo $field debe ser como mínimo $param.";
                    return false;
                }
                break;

            case 'max':
                if (is_string($value) && strlen($value) > (int)$param) {
                    $this->errors[$field] = "El campo $field debe tener como máximo $param caracteres.";
                    return false;
                }
                if (is_numeric($value) && $value > (int)$param) {
                    $this->errors[$field] = "El campo $field debe ser como máximo $param.";
                    return false;
                }
                break;

            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field] = "El campo $field debe ser un correo electrónico válido.";
                    return false;
                }
                break;

            case 'numeric':
                if (!is_numeric($value)) {
                    $this->errors[$field] = "El campo $field debe ser numérico.";
                    return false;
                }
                break;

            case 'rut':
                if (!$this->validateRut($value)) {
                    $this->errors[$field] = "El campo $field debe ser un RUT válido.";
                    return false;
                }
                break;

            case 'same':
                $other = $this->data[$param] ?? null;

                if ($value === null || $value === '' || $other === null || $other === '') {
                    //echo "<pre>[DEBUG] Comparación de campos vacíos en 'same': $field vs $param</pre>";
                    //exit;
                    $this->errors[$field] = "Ambos campos deben estar completos para compararlos.";
                    return false;
                }

                if ($value !== $other) {
                    //echo "<pre>[DEBUG] Campos distintos en 'same': $field != $param</pre>";
                    //exit;
                    $this->errors[$field] = "El campo $field debe ser igual al campo $param.";
                    return false;
                }
                break;

            case 'strong_password':
                if ($value === null || $value === '' || !PasswordStrengthValidator::isValid($value)) {
                    //echo "<pre>[DEBUG] Fallo en strong_password para $field (valor: $value)</pre>";
                    //exit;
                    $this->errors[$field] = "La contraseña debe tener al menos 8 caracteres, una letra mayúscula y un número.";
                    return false;
                }
                break;

            case 'image':
                $file = $_FILES[$field] ?? null;
                if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
                    $this->errors[$field] = "No se pudo subir la imagen para $field.";
                    return false;
                }
                if (getimagesize($file['tmp_name']) === false) {
                    $this->errors[$field] = "El archivo de $field no es una imagen válida.";
                    return false;
                }
                break;

            case 'mimes':
                $file = $_FILES[$field] ?? null;
                if ($file && $file['error'] === UPLOAD_ERR_OK) {
                    $allowed = explode(',', strtolower($param));
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    if (!in_array($ext, $allowed)) {
                        $this->errors[$field] = "El archivo $field debe ser de tipo: $param.";
                        return false;
                    }
                }
                break;

            case 'maxsize':
                $file = $_FILES[$field] ?? null;
                if ($file && $file['error'] === UPLOAD_ERR_OK) {
                    $maxBytes = $this->convertToBytes($param);
                    if ($file['size'] > $maxBytes) {
                        $this->errors[$field] = "El archivo $field excede el tamaño máximo de $param.";
                        return false;
                    }
                }
                break;

            case 'url':
                if (!filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->errors[$field] = "El campo $field debe ser una URL válida.";
                    return false;
                }

                $parts = parse_url($value);

                // Solo se permiten http o https
                if (!isset($parts['scheme']) || !in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
                    $this->errors[$field] = "El campo $field debe comenzar con http:// o https://";
                    return false;
                }

                // No se permiten rutas, parámetros ni fragmentos
                if (!empty($parts['path']) || !empty($parts['query']) || !empty($parts['fragment'])) {
                    $this->errors[$field] = "El campo $field debe ser solo la dirección principal del sitio (sin rutas ni parámetros).";
                    return false;
                }

                break;

            case 'active_url':
                if (!filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->errors[$field] = "El campo $field debe ser una URL válida.";
                    return false;
                }
                $host = parse_url($value, PHP_URL_HOST);

                // Soporte para dominios con tildes/IDN
                if (function_exists('idn_to_ascii') && $host) {
                    $host = idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46) ?: $host;
                }

                $hasDns = $host && (
                    checkdnsrr($host, 'A') ||
                    checkdnsrr($host, 'AAAA') ||
                    checkdnsrr($host, 'CNAME')
                );

                if (!$hasDns) {
                    $this->errors[$field] = "El dominio de $field no tiene registros DNS válidos.";
                    return false;
                }
                break;

            case 'string':
                if (!is_string($value)) {
                    $this->errors[$field] = "El campo $field debe ser una cadena de texto.";
                    return false;
                }

                // Opcional: podrías además validar que no sea solo números
                if (is_numeric($value)) {
                    $this->errors[$field] = "El campo $field no debe ser solo numérico.";
                    return false;
                }
                break;

            case 'date':
                // Valida formato fecha estándar YYYY-MM-DD (para tu campo DATE)
                if (!$this->validateDateFormat($value, 'Y-m-d')) {
                    $this->errors[$field] = "El campo $field debe ser una fecha válida (YYYY-MM-DD).";
                    return false;
                }
                break;

            case 'date_format':
                // Permite usar un formato personalizado, ej: date_format:d/m/Y
                if (!$param) {
                    throw new \InvalidArgumentException("La regla date_format requiere un formato, por ejemplo date_format:Y-m-d.");
                }

                if (!$this->validateDateFormat($value, $param)) {
                    $this->errors[$field] = "El campo $field no cumple el formato de fecha/hora $param.";
                    return false;
                }
                break;

            case 'time':
                // Valida hora en formato 24h: HH:MM o HH:MM:SS
                if (!is_string($value) || !preg_match('/^(2[0-3]|[01]\d):[0-5]\d(:[0-5]\d)?$/', $value)) {
                    $this->errors[$field] = "El campo $field debe ser una hora válida (HH:MM o HH:MM:SS).";
                    return false;
                }
                break;

            case 'unique':
                if (!$param) {
                    throw new \InvalidArgumentException("La regla unique requiere parámetros: unique:table,column[,exceptId].");
                }

                $parts = explode(',', $param);
                $table = trim($parts[0]);
                $column = isset($parts[1]) && $parts[1] !== '' ? trim($parts[1]) : $field;
                $exceptId = isset($parts[2]) && $parts[2] !== '' ? trim($parts[2]) : null;

                $query = Capsule::table($table)->where($column, $value);

                if ($exceptId !== null) {
                    $query->where('id', '<>', $exceptId);
                }

                if ($query->exists()) {
                    $this->errors[$field] = "El campo $field ya está en uso.";
                    return false;
                }
                break;
            // Puedes agregar más reglas aquí según necesidad


            default:
                // Regla desconocida, puedes lanzar excepción o ignorar
                break;
        }

        return true;
    }

    public function firstError(): string
    {
        return reset($this->errors) ?: '';
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    protected function validateRut($rut): bool
    {
        $rut = preg_replace('/[^0-9kK]/', '', $rut);
        if (strlen($rut) < 2) return false;

        $dv = strtoupper(substr($rut, -1));
        $num = substr($rut, 0, -1);
        $sum = 0;
        $factor = 2;

        foreach (array_reverse(str_split($num)) as $digit) {
            $sum += $digit * $factor;
            $factor = ($factor == 7) ? 2 : $factor + 1;
        }

        $calculatedDv = 11 - ($sum % 11);

        if ($calculatedDv == 11) {
            $calculatedDv = '0';
        } elseif ($calculatedDv == 10) {
            $calculatedDv = 'K';
        } else {
            $calculatedDv = (string) $calculatedDv;
        }

        return $dv === $calculatedDv;
    }

    private function convertToBytes($size)
    {
        $unit = strtolower(substr($size, -1));
        $bytes = (int)$size;
        switch ($unit) {
            case 'k':
                $bytes *= 1024;
                break;
            case 'm':
                $bytes *= 1048576;
                break;
            case 'g':
                $bytes *= 1073741824;
                break;
        }
        return $bytes;
    }

    protected function validateDateFormat($value, string $format): bool
    {
        // Si viene vacío, dejamos que la regla 'required' se encargue
        if ($value === null || $value === '') {
            return true;
        }

        $dt = \DateTime::createFromFormat($format, $value);

        // createFromFormat puede aceptar cosas raras, aseguramos coincidencia exacta
        return $dt && $dt->format($format) === $value;
    }
}
