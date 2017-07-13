<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','avatar','gender','about','position'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function messages()
    {
        return $this->hasMany('App\Message');
    }

    public function removeAvatar()
    {
        if($this->avatar){
            $imgName = explode('/',$this->avatar);
            $imgName = $imgName[count($imgName) -1];
            if($imgName != 'female.jpg' && $imgName != 'male.jpg') {
                unlink(storage_path('app/public/images/avatars/').$imgName);
            }
        }
    }
}
