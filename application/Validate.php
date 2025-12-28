<?php
namespace application;

use application\Filter;

class Validate
{
    /**
     * Valida y retorna la instancia del modelo.
     *
     * @param string $model Nombre de la clase modelo (ej: Role::class)
     * @param mixed $idOrInstance Id del registro o la instancia del modelo
     * @param string $route Ruta a redirigir si no existe
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function validateModel($model, $idOrInstance, $route)
    {
        // Si ya nos pasaron una instancia, la devolvemos
        if (is_object($idOrInstance)) {
            return $idOrInstance;
        }
        
        // Si recibimos un id, buscamos el modelo
        if ($idOrInstance) {
            $instance = $model::find((int) $idOrInstance);
            if ($instance) {
                return $instance;
            }
        }
        //Helper::debuger($instance);

        // Si no existe, redirigimos al listado (comportamiento previo)
        header('Location: ' . BASE_URL . $route);
        exit;
    }
}
