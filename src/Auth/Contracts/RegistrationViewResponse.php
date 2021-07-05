<?php


    declare(strict_types = 1);


    namespace BetterWP\Auth\Contracts;

    use BetterWP\Contracts\ResponsableInterface;
    use BetterWP\Http\Psr7\Request;

    abstract class RegistrationViewResponse implements ResponsableInterface
    {

        /**
         * @var Request
         */
        protected $request;

        public function setRequest(Request $request ) : RegistrationViewResponse
        {
            $this->request = $request;
            return $this;
        }
    }