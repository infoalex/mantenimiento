<?php namespace backend\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		'backend\Console\Commands\AllMakeCommand',
		'backend\Console\Commands\AdminControllerMakeCommand',
		'backend\Console\Commands\ApiControllerMakeCommand',
		'backend\Console\Commands\RepositoryMakeCommand',
		'backend\Console\Commands\RequestMakeCommand',
		'backend\Console\Commands\FormMakeCommand',
		'backend\Console\Commands\ModelMakeCommand',
		'backend\Console\Commands\ViewsControllerMakeCommand',
	];

}
