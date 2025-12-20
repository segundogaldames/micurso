<?php

namespace application;

use Exception;

class Session
{
	public static function init()
	{
		session_start();
	}

	#metodo que destruye una o varias variables de session
	public static function destroy($key = false)
	{
		if ($key) {
			if (is_array($key)) {
				foreach ($key as $k) {
					if (isset($_SESSION[$k])) {
						unset($_SESSION[$k]);
					}
				}
			} else {
				if (isset($_SESSION[$key])) {
					unset($_SESSION[$key]);
				}
			}
		} else {
			$_SESSION = [];
			session_destroy();
		}
	}

	#metodo que crea variables de session
	public static function set($key, $value)
	{
		if(!empty($key)){
			$_SESSION[$key] = $value;
		}
	}

	#metodo que lee una variable de session
	public static function get($key)
	{
		if(isset($_SESSION[$key])){
			return $_SESSION[$key];
		}
	}

	public static function resetId()
	{
		session_regenerate_id();
	}

	#metodo que define un tiempo de session
	public static function time()
	{
		if(!Session::get('time') || !defined('SESSION_TIME')){
			throw new Exception("Tiempo de session no definido");
		}

		if(SESSION_TIME == 0){
			//se asume que el tiempo de session es indefinido
			return;
		} 

		if(time() - Session::get('time') > (SESSION_TIME * 60)){
			Session::destroy();
			header('Location: ' . BASE_URL . 'login/logout');
			exit;
		}
		else{
			Session::set('time', time());
		}
	}
}