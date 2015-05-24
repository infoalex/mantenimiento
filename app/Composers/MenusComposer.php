<?php namespace App\Composers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MenusComposer {

	protected $menus;
	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;

		$this->menus = [
			'users' => [
				'visible' => true,
				'icon' => 'fa-users',
				'edit' => true,
				'name' => trans('messages.users.index')],
			'roles' => [
    			'visible' => true,
    			'icon' => 'fa-file-o',
    			'edit' => true,
    			'name' => trans('messages.roles.index')]		
		];
	}


	public function compose(View $view)
	{
		$view->with(['menus' => $this->menus, 'active' => $this->request->segment(2)]);
	}

}