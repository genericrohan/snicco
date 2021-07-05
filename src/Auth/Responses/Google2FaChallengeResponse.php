<?php


    declare(strict_types = 1);


    namespace BetterWP\Auth\Responses;

    use BetterWP\Auth\Contracts\TwoFactorChallengeResponse;
    use BetterWP\Http\ResponseFactory;

    class Google2FaChallengeResponse extends TwoFactorChallengeResponse
    {

        /**
         * @var ResponseFactory
         */
        private $response_factory;

        public function __construct(ResponseFactory $response_factory)
        {
            $this->response_factory = $response_factory;
        }

        public function toResponsable()
        {

            return $this->response_factory->redirect()->toRoute('auth.2fa.challenge');


        }

    }