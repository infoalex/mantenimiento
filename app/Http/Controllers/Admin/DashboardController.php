<?php namespace backend\Http\Controllers\Admin;

use backend\Http\Requests;
use backend\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller {

	public function index()
	{
		return view('dashboard.index');
	}

public function inicio()
    {
        return view('dashboard.inicio');
    }
}
