<?php


	declare( strict_types = 1 );


	namespace WPEmerge\ServiceProviders;

	use Psr\Http\Message\ServerRequestInterface;
    use WPEmerge\Contracts\ErrorHandlerInterface;
	use WPEmerge\Contracts\RequestInterface;
	use WPEmerge\Contracts\ServiceProvider;
	use WPEmerge\Exceptions\NullErrorHandler;
	use WPEmerge\Exceptions\ProductionErrorHandler;
	use WPEmerge\Factories\ErrorHandlerFactory;
    use WPEmerge\Http\Request;

    class ExceptionServiceProvider extends ServiceProvider {

		public function register() : void {

			$this->container->instance(ProductionErrorHandler::class, ProductionErrorHandler::class);

			$this->container->singleton( ErrorHandlerInterface::class, function () {

				if ( ! $this->config->get('exception_handling.enable', false ) ) {

					return new NullErrorHandler();

				}

				/** @var RequestInterface $request */
				$request = $this->container->make( Request::class );

				return ErrorHandlerFactory::make(
					$this->container,
					$this->config->get( 'exception_handling.debug', false ),
					$request->isAjax(),
					$this->config->get( 'exception_handling.editor', 'phpstorm' )

				);
			});

		}

		public function bootstrap() : void {

			$error_handler = $this->container->make( ErrorHandlerInterface::class );

			$error_handler->register();

			$this->container->instance( ErrorHandlerInterface::class, $error_handler );

		}

	}