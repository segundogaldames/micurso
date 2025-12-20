<?php
namespace application;

use application\Request;

class Bootstrap
{
    public static function run(Request $request)
    {
		$baseControllerPath = APP_PATH . 'Controller.php';
		//print_r($baseControllerPath);exit;
		if (is_readable($baseControllerPath)) {
			require_once $baseControllerPath;
		} else {
			exit("Error crítico: no se pudo cargar la clase base Controller.php");
		}

        $module = $request->getModule();
        $controllerName = ucfirst($request->getController()) . 'Controller';
		//print_r($controllerName);exit;
        $method = $request->getMethod();
        $args = $request->getArgs();

        try {
            if ($module) {
                $controllerPath = ROOT . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $controllerName . '.php';
                $controllerClass = "modules\\$module\\controllers\\$controllerName";
            } else {
                $controllerPath = ROOT . 'controllers' . DIRECTORY_SEPARATOR . $controllerName . '.php';
                //print_r($controllerPath);exit;
                $controllerClass = "controllers\\$controllerName";
            }

            if (!is_readable($controllerPath)) {
                throw new \Exception("Archivo de controlador no legible: $controllerPath");
            }

            require_once $controllerPath;

            if (!class_exists($controllerClass)) {
                throw new \Exception("Clase de controlador no encontrada: $controllerClass");
            }

            $controller = new $controllerClass();

            if (!is_callable([$controller, $method])) {
                throw new \Exception("Método '$method' no existe o no es accesible en el controlador $controllerClass.");
            }

            call_user_func_array([$controller, $method], $args);
        } catch (\Exception $e) {
            self::handleError($e->getMessage(), $request);
        }
    }

    private static function handleError($message, Request $request)
    {
        // Evita redirección infinita si el error ocurre dentro del controlador de error
        if ($request->getController() === 'error') {
            exit("Error crítico: $message");
        }

        // Intentamos cargar el controlador de error
        $errorControllerPath = ROOT . 'controllers/ErrorController.php';
        $errorControllerClass = 'controllers\\ErrorController';

        if (is_readable($errorControllerPath)) {
            require_once $errorControllerPath;
            if (class_exists($errorControllerClass)) {
                $errorController = new $errorControllerClass();
                if (method_exists($errorController, 'error')) {
                    $errorController->error($message);
                    return;
                }
            }
        }

        // Si todo falla
        exit("Error crítico: no se pudo cargar ni el controlador solicitado ni el controlador de error.<br>$message");
    }
}
