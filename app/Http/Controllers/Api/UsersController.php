<?php namespace backend\Http\Controllers\Api;

use Illuminate\Http\Request;

use backend\Http\Controllers\Controller;
use backend\Repositories\UserRepository as User;

class UsersController extends Controller {

	/**
	 * Repostory user
	 *
	 * @var UserRepository
	 */
	private $user;

	/**
	 * Construc controller.
	 *
	 * @param  User $user
	 */
	public function __construct(User $user)
	{
		$this->user = $user;
	}

	/**
	 * Display a listing of the resource.
	 *
     * @param  Request  $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		$results = $this->user->search($request);

		return $results;
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		$user = $this->user->getModel()->findOrFail($id);

		return $user;
	}

}