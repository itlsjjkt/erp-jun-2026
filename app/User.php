<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Department;
use App\Models\Workarea;
use App\Models\Group;
use App\Models\Level;
use App\Models\Company;
use App\Models\Position;


use Hash;

class User extends Authenticatable
{
    use Notifiable;
    use HasRoles;
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password','company_id','location_id', 'remember_token','role','photo','type','data_access','notification_email','is_create','is_edit','is_delete','background','is_mobile','department_id','dashboard','ttd','is_whatsapp','telp'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    /*
    |------------------------------------------------------------------------------------
    | Validations
    |------------------------------------------------------------------------------------
    */
    public static function rules($update = false, $id = null)
    {
        $commun = [
            'email'    => "required|email|unique:users,email,$id",
            'password' => 'nullable|confirmed',
            'avatar' => 'image',
        ];

        if ($update) {
            return $commun;
        }

        return array_merge($commun, [
            'email'    => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }
    /*
    |------------------------------------------------------------------------------------
    | Attributes
    |------------------------------------------------------------------------------------
    */
    public function setPasswordAttribute($value='')
    {
        $this->attributes['password'] = $value;
    }

    public function getAvatarAttribute($value)
    {
        if (!$value) {
            return 'http://placehold.it/160x160';
        }

        return config('variables.avatar.public').$value;
    }
    public function setAvatarAttribute($photo)
    {
        $this->attributes['avatar'] = move_file($photo, 'avatar');
    }

    /*
    |------------------------------------------------------------------------------------
    | Boot
    |------------------------------------------------------------------------------------
    */
    public static function boot()
    {
        parent::boot();
        static::updating(function ($user) {
            $original = $user->getOriginal();

            if (\Hash::check('', $user->password)) {
                $user->attributes['password'] = $original['password'];
            }
        });
    }

    public function role()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }


    public function company($id)
    {
        $result = Company::where('id', $id)->first();
        if($result ){
            return $result->name;
        }else{
            return false;
        }
    }

    public function workarea($id)
    {
        $result = Workarea::where('id', $id)->first();
        if($result ){
            return $result->name;
        }else{
            return false;
        }
    }

    public function group($id)
    {
        $result =  Group::where('id', $id)->first();
        if($result ){
            return $result->name;
        }else{
            return false;
        }
    }

    public function position($id)
    {
        $result =  Position::where('id', $id)->first();
        if($result ){
            return $result->name;
        }else{
            return false;
        }
    }

    public function level($id)
    {
        $result =  Level::where('id', $id)->first();
        if($result ){
            return $result->name;
        }else{
            return false;
        }
    }

    public function department($id)
    {
        $result =  Department::where('id', $id)->first();
        if($result ){
            return $result->name;
        }else{
            return false;
        }
    }


    public function searchableAs()
    {
        return 'users';
    }



}
