<?php


    declare(strict_types = 1);


    namespace WPEmerge\Http\Psr7;

    use Psr\Http\Message\ServerRequestInterface;
    use Psr\Http\Message\UriInterface;
    use WPEmerge\Facade\WP;
    use WPEmerge\Http\Psr7\InspectsRequest;
    use WPEmerge\Http\Psr7\ImplementsPsr7Request;
    use WPEmerge\Routing\RoutingResult;
    use WPEmerge\Session\SessionStore;
    use WPEmerge\Support\Arr;
    use WPEmerge\Support\Str;
    use WPEmerge\Support\VariableBag;

    class Request implements ServerRequestInterface
    {
        use ImplementsPsr7Request;
        use InspectsRequest;

        public function __construct(ServerRequestInterface $psr_request)
        {

            $this->psr_request = $psr_request;

        }

        public function withType ( string $type ) {

            return $this->withAttribute('type', $type);

        }


        /**
         * This method stores the URI that is used for matching against the routes
         * inside the AbstractRouteCollection. This URI is modified inside the CORE Middleware
         * for wp-admin and admin-ajax routes
         * to provide a more friendly api for matching these type of routes.
         *
         * For admin routes the [page] query parameter is appended to the wp-admin url.
         * For ajax routes the [action] query parameter is appended to the admin-ajax url.
         *
         * This is stored in an additional attribute to not tamper with the "real" requested URL.
         *
         * This URI shall not be used anymore BESIDES FOR MATCHING A ROUTE.
         *
         */
        public function withRoutingUri(UriInterface $uri)
        {
            return $this->withAttribute('routing.uri', $uri);
        }

        public function withRoutingResult(RoutingResult $routing_result) {

            return $this->withAttribute('routing.result', $routing_result);

        }

        public function withCookies(array $cookies) {

            return $this->withAttribute('cookies', new VariableBag($cookies));

        }

        public function withSession (SessionStore $session_store) {

            return $this->withAttribute('session', $session_store);

        }

        public function getPath() : string
        {
            return $this->getUri()->getPath();
        }

        public function getFullPath() : string
        {

           return $this->getRequestTarget();

        }

        public function getUrl( bool $trailing_slash = false ) : string
        {
            $url = trim(preg_replace('/\?.*/', '', $this->getUri()), '/');

            if ( $trailing_slash ) {

                $url = $url . '/';

            }

            return $url;

        }

        public function getFullUrl(bool $trailing_slash = false) : string
        {

            $full_url = trim($this->getUri()->__toString());

            return  ($trailing_slash) ? $full_url . '/' : $full_url;

        }

        public function getRoutingPath () : string
        {

            $uri = $this->getAttribute('routing.uri', null);

            /** @var UriInterface $uri */
            $uri = $uri ?? $this->getUri();

            return $uri->getPath();

        }

        public function getType() : string
        {

            return $this->getAttribute('type', '');

        }

        public function getQuery(string $name = null , $default = null )
        {

            if ( ! $name ) {

                return $this->getQueryParams() ?? [];

            }

            return Arr::get($this->getQueryParams(), $name, $default);

        }

        public function getBody(string $name = null , $default = null )
        {

            if ( ! $name ) {

                return $this->getParsedBody() ?? [];

            }

            return Arr::get($this->getParsedBody(),$name, $default);

        }

        public function getRoutingResult () :RoutingResult {

            return $this->getAttribute('routing.result', new RoutingResult(null, []));

        }

        public function getCookies() : VariableBag
        {
            return $this->getAttribute('cookies', new VariableBag());
        }

        public function getSession () :?SessionStore {

            return $this->getAttribute('session');

        }



    }