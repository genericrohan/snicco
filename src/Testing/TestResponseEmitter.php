<?php


    declare(strict_types = 1);


    namespace BetterWP\Testing;

    use Psr\Http\Message\ResponseInterface;
    use BetterWP\Http\Cookies;
    use BetterWP\Http\ResponseEmitter;

    class TestResponseEmitter extends ResponseEmitter
    {

        /**
         * @var ResponseInterface
         */
        public $response;

        /**
         * @var Cookies
         */
        public $cookies;


        public function emit(ResponseInterface $response) : void
        {
            $this->response = new TestResponse($response);
        }

        public function emitCookies(Cookies $cookies)
        {
            $this->cookies = $cookies;
        }

        public function emitHeaders(ResponseInterface $response) : void
        {

            //

        }

    }