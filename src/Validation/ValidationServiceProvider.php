<?php


    declare(strict_types = 1);


    namespace WPEmerge\Validation;

    use Respect\Validation\Factory;
    use WPEmerge\Contracts\ServiceProvider;
    use WPEmerge\Validation\Middleware\ShareValidatorWithRequest;

    class ValidationServiceProvider extends ServiceProvider
    {

        public function register() : void
        {
            $this->bindConfig();
            $this->bindValidator();
            $this->addRuleNamespace();
        }

        function bootstrap() : void
        {
        }

        private function bindValidator()
        {

            $this->container->singleton(Validator::class, function () {

                $validator = new Validator();
                $validator->globalMessages($this->config->get('validation.messages'));

                return $validator;

            });

        }

        private function bindConfig()
        {

            $this->config->extend('validation.messages', []);
            $this->config->extend('middleware.groups.global', [ShareValidatorWithRequest::class]);
            $this->config->extend('middleware.unique', [ShareValidatorWithRequest::class]);
        }

        private function addRuleNamespace()
        {
            Factory::setDefaultInstance(
                (new Factory())
                    ->withRuleNamespace('WPEmerge\Validation\Rules')
                    ->withExceptionNamespace('WPEmerge\Validation\Exceptions')
            );
        }

    }