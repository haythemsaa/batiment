<?php
/**
 * Routeur de l'application
 */
class Router
{
    private $routes = [];
    private $middleware = [];
    private $groupPrefix = '';
    private $groupMiddleware = [];

    /**
     * Ajoute une route GET
     */
    public function get($uri, $controller)
    {
        $this->addRoute('GET', $uri, $controller);
    }

    /**
     * Ajoute une route POST
     */
    public function post($uri, $controller)
    {
        $this->addRoute('POST', $uri, $controller);
    }

    /**
     * Ajoute une route
     */
    private function addRoute($method, $uri, $controller)
    {
        $uri = $this->groupPrefix . $uri;
        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'controller' => $controller,
            'middleware' => $this->groupMiddleware
        ];
    }

    /**
     * Groupe de routes avec middleware et/ou prefix
     */
    public function group($options, $callback)
    {
        $previousPrefix = $this->groupPrefix;
        $previousMiddleware = $this->groupMiddleware;

        if (isset($options['prefix'])) {
            $this->groupPrefix .= '/' . trim($options['prefix'], '/');
        }

        if (isset($options['middleware'])) {
            $this->groupMiddleware = array_merge(
                $this->groupMiddleware,
                (array) $options['middleware']
            );
        }

        $callback($this);

        $this->groupPrefix = $previousPrefix;
        $this->groupMiddleware = $previousMiddleware;
    }

    /**
     * Dispatch la requête vers le bon contrôleur
     */
    public function dispatch()
    {
        $uri = $this->getUri();
        $method = $_SERVER['REQUEST_METHOD'];

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = $this->convertUriToRegex($route['uri']);

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Retire le match complet

                // Exécution des middlewares
                foreach ($route['middleware'] as $middleware) {
                    $middlewareClass = ucfirst($middleware) . 'Middleware';
                    if (class_exists($middlewareClass)) {
                        $middlewareInstance = new $middlewareClass();
                        if (!$middlewareInstance->handle()) {
                            return;
                        }
                    }
                }

                // Exécution du contrôleur
                $this->callController($route['controller'], $matches);
                return;
            }
        }

        // Route non trouvée
        $this->notFound();
    }

    /**
     * Convertit l'URI en expression régulière
     */
    private function convertUriToRegex($uri)
    {
        // Remplace {param} par un groupe de capture
        $pattern = preg_replace('/\{(\w+)\}/', '([^/]+)', $uri);
        return '#^' . $pattern . '$#';
    }

    /**
     * Appelle le contrôleur
     */
    private function callController($controllerAction, $params = [])
    {
        list($controller, $action) = explode('@', $controllerAction);

        if (!class_exists($controller)) {
            die("Controller {$controller} not found");
        }

        $controllerInstance = new $controller();

        if (!method_exists($controllerInstance, $action)) {
            die("Method {$action} not found in {$controller}");
        }

        call_user_func_array([$controllerInstance, $action], $params);
    }

    /**
     * Récupère l'URI courante
     */
    private function getUri()
    {
        $uri = $_SERVER['REQUEST_URI'];

        // Retire les paramètres de requête
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        return rtrim($uri, '/') ?: '/';
    }

    /**
     * Page 404
     */
    private function notFound()
    {
        http_response_code(404);
        if (file_exists(__DIR__ . '/../Views/errors/404.php')) {
            require __DIR__ . '/../Views/errors/404.php';
        } else {
            echo '<h1>404 - Page Not Found</h1>';
        }
    }
}
