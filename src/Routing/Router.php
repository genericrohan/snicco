<?php


    declare(strict_types = 1);


    namespace BetterWP\Routing;

    use Closure;
    use Contracts\ContainerAdapter;
    use BetterWP\Contracts\AbstractRouteCollection;
    use BetterWP\Controllers\FallBackController;
    use BetterWP\Controllers\RedirectController;
    use BetterWP\Controllers\ViewController;
    use BetterWP\Support\WP;
    use BetterWP\Support\Str;
    use BetterWP\Support\Url;
    use BetterWP\Traits\HoldsRouteBlueprint;

    /**
     * @mixin RouteDecorator
     */
    class Router
    {

        use HoldsRouteBlueprint;

        /** @var RouteGroup[] */
        private $group_stack = [];

        /** @var ContainerAdapter */
        private $container;

        /** @var AbstractRouteCollection */
        private $routes;

        /**
         * @var bool
         */
        private $force_trailing;

        public function __construct(ContainerAdapter $container, AbstractRouteCollection $routes, bool $force_trailing = false)
        {

            $this->container = $container;
            $this->routes = $routes;
            $this->force_trailing = $force_trailing;

        }

        public function view(string $url, string $view, array $data = [], int $status = 200, array $headers = []) : Route
        {

            $route = $this->match(['GET', 'HEAD'], $url, ViewController::class.'@handle');
            $route->defaults([
                'view' => $view,
                'data' => $data,
                'status' => $status,
                'headers' => $headers,
            ]);

            return $route;

        }

        public function redirect(string $url, string $location, int $status = 302, bool $secure = true, bool $absolute = false) : Route
        {

            return $this->any($url, [RedirectController::class, 'to'])->defaults([
                'location' => $location,
                'status' => $status,
                'secure' => $secure,
                'absolute' => $absolute,
            ]);

        }

        public function permanentRedirect(string $url, string $location, bool $secure = true, bool $absolute = false) : Route
        {

            return $this->redirect($url, $location, 301, $secure, $absolute);

        }

        public function temporaryRedirect(string $url, string $location, bool $secure = true, bool $absolute = false) : Route
        {

            return $this->redirect($url, $location, 307, $secure, $absolute);

        }

        public function redirectAway(string $url, string $location, int $status = 302) : Route
        {

            return $this->any($url, [RedirectController::class, 'away'])->defaults([
                'location' => $location,
                'status' => $status,
            ]);

        }

        public function redirectToRoute(string $url, string $route, array $params = [], int $status = 302) : Route
        {

            return $this->any($url, [RedirectController::class, 'toRoute'])->defaults([
                'route' => $route,
                'status' => $status,
                'params' => $params,
            ]);
        }

        public function addRoute(array $methods, string $path, $action = null, $attributes = []) : Route
        {

            $url = $this->applyPrefix($path);

            $url = $this->formatTrailing($url);

            $route = new Route($methods, $url, $action);

            if ($this->hasGroupStack()) {

                $this->mergeGroupIntoRoute($route);

            }

            if ( ! empty($attributes)) {

                $this->populateInitialAttributes($route, $attributes);

            }

            return $this->routes->add($route);


        }

        public function group(array $attributes, Closure $routes)
        {

            $this->updateGroupStack(new RouteGroup($attributes));

            $this->registerRoutes($routes);

            $this->deleteLastRouteGroup();

        }

        private function registerRoutes(Closure $routes)
        {

            $routes($this);

        }

        public function loadRoutes(bool $global_routes = false)
        {

            if ( ! $this->hasGroupStack()) {


                $this->routes->loadIntoDispatcher($global_routes);


            }


        }

        public function createFallbackWebRoute()
        {


            $this->any('/{fallback}', [FallBackController::class, 'handle'])
                 ->and('fallback', '[^.]+')
                 ->where(function () {

                     return ! WP::isAdmin();

                 });

        }

        public function __call($method, $parameters)
        {

            if ( ! in_array($method, RouteDecorator::allowed_attributes)) {

                throw new \BadMethodCallException(
                    'Method: '.$method.' does not exists on '.get_class($this)
                );

            }

            if ($method === 'where' || $method === 'middleware') {

                return ((new RouteDecorator($this))->decorate(
                    $method,
                    is_array($parameters[0]) ? $parameters[0] : $parameters)
                );

            }

            if ($method === 'noAction') {

                return ((new RouteDecorator($this))->decorate($method, true));

            }

            return ((new RouteDecorator($this))->decorate($method, $parameters[0]));

        }

        public function fallback(callable $fallback_handler)
        {

            /** @var FallBackController $controller */
            $controller = $this->container->make(FallBackController::class);
            $controller->setFallbackHandler($fallback_handler);
            $this->container->instance(FallBackController::class, $controller);

        }

        private function populateInitialAttributes(Route $route, array $attributes)
        {

            ((new RouteAttributes($route)))->populateInitial($attributes);
        }

        private function deleteLastRouteGroup()
        {

            array_pop($this->group_stack);

        }

        private function updateGroupStack(RouteGroup $group)
        {

            if ($this->hasGroupStack()) {

                $group = $this->mergeWithLastGroup($group);

            }

            $this->group_stack[] = $group;

        }

        private function hasGroupStack() : bool
        {

            return ! empty($this->group_stack);

        }

        private function mergeWithLastGroup(RouteGroup $new_group) : RouteGroup
        {

            return $new_group->mergeWith($this->lastGroup());

        }

        private function applyPrefix(string $url) : string
        {

            if ( ! $this->hasGroupStack()) {

                return $url;

            }

            $url = $this->maybeStripTrailing($url);

            return Url::combineRelativePath($this->lastGroupPrefix(), $url);

        }

        private function mergeGroupIntoRoute(Route $route)
        {

            (new RouteAttributes($route))->mergeGroup($this->lastGroup());

        }

        private function lastGroup()
        {

            return end($this->group_stack);

        }

        private function lastGroupPrefix() : string
        {

            if ( ! $this->hasGroupStack()) {

                return '';

            }

            return $this->lastGroup()->prefix();


        }

        private function maybeStripTrailing(string $url) : string
        {

            if (trim($this->lastGroupPrefix(), '/') === WP::wpAdminFolder()) {

                return rtrim($url, '/');

            }

            if (trim($this->lastGroupPrefix(), '/') === WP::ajaxUrl()) {

                return rtrim($url, '/');

            }

            return $url;


        }

        private function formatTrailing(string $url) : string
        {

            if (Str::contains($url, WP::wpAdminFolder())) {
                return rtrim($url, '/');
            }

            if ( ! $this->force_trailing) {
                return $url;
            }

            if ( $url === '/{fallback}') {
                return $url;
            }

            return Url::addTrailing($url);

        }

    }

