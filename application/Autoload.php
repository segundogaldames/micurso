<?php

namespace application;

function autoloadCore($class)
{
    // Reemplaza namespace (\) por separador de directorios
    $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $class);

    // Normaliza el nombre de archivo (por ejemplo, hace ucfirst al nombre de clase)
    // Para esto, separo ruta y nombre archivo
    $pathParts = explode(DIRECTORY_SEPARATOR, $classPath);
    $fileName = array_pop($pathParts);
    $fileName = ucfirst(strtolower($fileName));

    $dirPath = implode(DIRECTORY_SEPARATOR, $pathParts);

    // Construye la ruta final al archivo
    $file = APP_PATH . $dirPath . DIRECTORY_SEPARATOR . $fileName . '.php';

    if (file_exists($file)) {
        include_once $file;
    }
}

spl_autoload_register('application\autoloadCore');

