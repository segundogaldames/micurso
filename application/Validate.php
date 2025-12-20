<?php
namespace application;

use application\Filter;

class Validate
{
    public static function validateModel($model, $id, $route)
    {
        //print_r($route);exit;
        if ($id) {
            $instance = $model::select('id')->find((int) $id);

            if ($instance) {
                return true;
            }
        }

        header('Location: ' . BASE_URL . $route);
    }
}
