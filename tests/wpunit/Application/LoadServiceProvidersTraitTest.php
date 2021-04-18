<?php


	namespace WPEmergeTests\wpunit\Application;

	use Codeception\TestCase\WPTestCase;
	use WPEmerge\ServiceProviders\ApplicationServiceProvider;
	use WPEmerge\Controllers\ControllersServiceProvider;
	use WPEmerge\Csrf\CsrfServiceProvider;
	use WPEmerge\ServiceProviders\ExceptionsServiceProvider;
	use WPEmerge\ServiceProviders\FlashServiceProvider;
	use WPEmerge\ServiceProviders\KernelsServiceProvider;
	use WPEmerge\ServiceProviders\MiddlewareServiceProvider;
	use WPEmerge\ServiceProviders\RequestsServiceProvider;
	use WPEmerge\ServiceProviders\ResponsesServiceProvider;
	use WPEmerge\ServiceProviders\RoutingServiceProvider;
	use WPEmerge\ServiceProviders\OldInputServiceProvider;
	use WPEmerge\ServiceProviders\ViewServiceProvider;
	use WPEmergeTestTools\TestApp;
	use WPEmergeTestTools\TestProvider1;
	use WPEmergeTestTools\TestProvider2;
	use WPEmergeTestTools\TestService;

	class LoadServiceProvidersTraitTest extends WPTestCase {

		private $service_providers = [

			ApplicationServiceProvider::class,
			KernelsServiceProvider::class,
			ExceptionsServiceProvider::class,
			RequestsServiceProvider::class,
			ResponsesServiceProvider::class,
			RoutingServiceProvider::class,
			ViewServiceProvider::class,
			ControllersServiceProvider::class,
			MiddlewareServiceProvider::class,
			CsrfServiceProvider::class,


			#
			FlashServiceProvider::class,

			#
			OldInputServiceProvider::class,


		];

		/** @test */
		public function all_core_service_providers_get_registered_correctly_in_the_container_and_can_be_merged_with_user_provided_ones() {

			$app = new TestApp();

			$user_config = [

				'providers' => [
					TestProvider1::class,
					TestProvider2::class,
				]
			];

			$app::make();
			$app::bootstrap($user_config);

			$container = $app::container();

			foreach ( $this->service_providers  as $service_provider ) {

				$this->assertTrue($container->offsetExists($service_provider), $service_provider . ' not found in container.');

			}

			$this->assertTrue($container->offsetExists(TestProvider1::class));
			$this->assertTrue($container->offsetExists(TestProvider2::class));


		}

		/** @test */
		public function an_exception_gets_thrown_if_a_service_provider_doesnt_implement_the_correct_interface() {


			$this->expectExceptionMessage('The following class does not implement');

			$app = new TestApp();

			$user_config = [

				'providers' => [
					TestService::class
				]
			];

			$app::make();
			$app::bootstrap($user_config);


		}

		/** @test */
		public function the_register_method_gets_called_correctly_with_the_container_instance(  ) {

			$app = new TestApp();


			$user_config = [

				'providers' => [
					TestProvider1::class,
				]
			];


			$app::make();
			$app::bootstrap($user_config);

			$this->assertSame('bar', $app::container()['foo']);


		}

		/** @test */
		public function the_boostrap_method_gets_called_correctly_with_the_container_instance() {

			$app = new TestApp();


			$user_config = [

				'providers' => [
					TestProvider1::class,
				]
			];


			$app::make();
			$app::bootstrap($user_config);

			$this->assertSame('baz', $app::container()['bar']);

		}


	}


