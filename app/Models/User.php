<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Notifications\Notifiable;
use Laratrust\Traits\LaratrustUserTrait;
use Auth;

class User extends \App\Models\Base\User implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    // Always remember to put necessary traits inside class after defining them below namespace
    // These traits are used by default for user login authentication
    use LaratrustUserTrait {
        LaratrustUserTrait::can insteadof Authorizable;
        Authorizable::can as authorizableCan;
    }
    use Authenticatable,
        Authorizable,
        CanResetPassword,
        Notifiable;

	protected $hidden = [
		'password',
		'remember_token'
	];

	protected $fillable = [
		'name',
		'first_name',
		'last_name',
		'second_last_name',
		'cedula',
		'email',
		'email_verified_at',
		'password',
		'remember_token',
        'image',
        'role',
	];

	/**
	 * Get the full name attribute
	 */
	public function getFullNameAttribute()
	{
		return trim($this->first_name . ' ' . $this->last_name . ' ' . $this->second_last_name);
	}

	/**
	 * Get the student (history) associated with this user
	 */
	public function history()
	{
		return $this->hasOne(History::class, 'user_id');
	}

	// Function get user image from database
	public function adminlte_image() {

	    $getImage = User::find(Auth::user()->id);
	    $image = asset('uploads/'.$getImage->image);

	    return $image;
    }

    public function adminlte_desc() {
        return 'Hi, Welcome!';
    }

    /**
     * Get the parent profile for the user.
     */
    public function parentProfile()
    {
        return $this->hasOne(\App\Models\ParentProfile::class);
    }
}
