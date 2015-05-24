<?php namespace App;
use Illuminate\Database\Eloquent\Model;
use Zizaco\Entrust\EntrustRole;
class Role extends EntrustRole {

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name'];

	/**
	 * Get the created date
	 *
	 * @return string
	 */
	public function getCreatedAttribute()
	{
		return \Date::parse($this->created_at)->format('d-m-Y');
	}

}