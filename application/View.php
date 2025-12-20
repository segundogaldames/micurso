<?php
namespace application;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;
use application\Flash; // Asumiendo que Flash está en libs
use application\Helper;

class View
{
    private $_controller;
    private $_twig;

    public function __construct(Request $request)
    {
        $this->_controller = $request->getController();

        // Carga del loader apuntando a la carpeta views
        $loader = new FilesystemLoader(ROOT . 'views');
        $this->_twig = new Environment($loader, [
            'debug' => true, // 👈 Esto activa el modo debug
            'cache' => false // o pon tu ruta de cache si la usas
        ]);

        $this->_twig->addExtension(new DebugExtension());

        // Variables globales disponibles en todas las vistas
        $this->_twig->addGlobal('BASE', BASE_URL);
        $this->_twig->addGlobal('session', $_SESSION);

        // Inyectar mensajes flash y limpiar después
        //$flashMessages = Flash::get();  // Recupera y limpia mensajes
        //$this->_twig->addGlobal('flash_messages', $flashMessages);
        $messages = Flash::get(); // recupera y limpia mensajes
        $classes = [
            'success' => 'flash-success',
            'error'   => 'flash-error',
            'warning' => 'flash-warning',
            'info'    => 'flash-info',
        ];

        $flashMessages = [];
        foreach ($messages as $tipo => $mensaje) {
            $flashMessages[] = [
                'type' => $tipo,
                'message' => $mensaje,
                'class' => $classes[$tipo] ?? $classes['info']
            ];
        }

        $this->_twig->addGlobal('flash_messages', $flashMessages);
    }

    /**
     * Renderiza una vista Twig con parámetros opcionales.
     *
     * @param string $view Nombre de la vista (sin extensión .twig)
     * @param array $params Variables a pasar a la vista
     */
    public function load(string $view, array $params = [])
    {
        echo $this->_twig->render($view . '.twig', $params);
    }
}
