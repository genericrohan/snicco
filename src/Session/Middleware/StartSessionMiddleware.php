<?php


    declare(strict_types = 1);


    namespace WPEmerge\Session\Middleware;

    use Carbon\Carbon;
    use Psr\Http\Message\ResponseInterface;
    use WPEmerge\Contracts\Middleware;
    use WPEmerge\Http\Cookies;
    use WPEmerge\Http\Delegate;
    use WPEmerge\Http\Psr7\Request;
    use WPEmerge\Http\Responses\NullResponse;
    use WPEmerge\Session\SessionStore;


    class StartSessionMiddleware extends Middleware
    {

        /**
         * @var SessionStore
         */
        private $session_store;

        /**
         * @var array|int[]
         */

        /**
         * @var array
         */
        private $config;

        /**
         * @var Cookies
         */
        private $cookies;

        public function __construct(SessionStore $session_store, Cookies $cookies, array $config)
        {

            $this->session_store = $session_store;
            $this->config = $config;
            $this->cookies = $cookies;

        }

        public function handle(Request $request, Delegate $next)
        {

            $this->collectGarbage();

            $this->startSession(
                $session = $this->getSession($request),
                $request

            );

            return $this->handleStatefulRequest($request, $session, $next);


        }

        private function getSession(Request $request) : SessionStore
        {

            $cookies = $request->getCookies();
            $cookie_name = $this->session_store->getName();

            $this->session_store->setId($cookies->get($cookie_name, ''));

            return $this->session_store;
        }

        private function startSession(SessionStore $session_store, Request $request)
        {

            $session_store->start();
            $session_store->getHandler()->setRequest($request);

        }

        private function handleStatefulRequest(Request $request, SessionStore $session, Delegate $next) : ResponseInterface
        {

            $request = $request->withSession($session);

            $response = $next($request);

            $this->storePreviousUrl($response, $request, $session);

            $this->addSessionCookie($session);

            $this->saveSession($session);

            return $response;

        }

        private function storePreviousUrl(ResponseInterface $response, Request $request, SessionStore $session)
        {

            if ($response instanceof NullResponse) {

                return;

            }

            if ($request->isGet() && ! $request->isAjax()) {

                $session->setPreviousUrl($request->getFullUrl());

            }


        }

        private function saveSession(SessionStore $session)
        {

            $session->save();
        }

        private function collectGarbage()
        {

            if ($this->configHitsLottery($this->config['lottery'])) {

                $this->session_store->getHandler()->gc($this->getSessionLifetimeInSeconds());

            }
        }

        private function configHitsLottery(array $lottery) : bool
        {

            return random_int(1, $lottery[1]) <= $lottery[0];
        }

        private function getSessionLifetimeInSeconds()
        {

            return $this->config['lifetime'] * 60;
        }

        private function addSessionCookie(SessionStore $session)
        {

            $this->cookies->set(
                $this->config['cookie'],
                [
                    'value' => $session->getId(),
                    'path' => $this->config['path'],
                    'samesite' => ucfirst($this->config['same_site']),
                    'expires' => Carbon::now()->addMinutes($this->config['lifetime'])->getTimestamp(),
                    'httponly' => $this->config['http_only'],
                    'secure' => $this->config['secure'],
                    'domain' => $this->config['domain']

                ]
            );
        }

    }