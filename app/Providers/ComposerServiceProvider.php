<?php namespace backend\Providers;

use Illuminate\Support\ServiceProvider;

class ComposerServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		view()->composer('layout.partials.content', 'backend\Composers\RouteComposer');
		view()->composer('layout.partials.table', 'backend\Composers\RouteComposer');
		view()->composer('layout.partials.form', 'backend\Composers\RouteComposer');
		view()->composer('layout.partials.show', 'backend\Composers\RouteComposer');

		view()->composer('layout.partials.nav.profile', 'backend\Composers\AuthComposer');
		view()->composer('layout.partials.sidebar.user', 'backend\Composers\AuthComposer');

		view()->composer('layout.partials.sidebar.menu', 'backend\Composers\MenusComposer');
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}

}
