<?php

namespace App;

use App\Payment;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use Notifiable;
   // use SoftDeletes;

   
    protected $table = 'users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    //protected $fillable = ['vk_id', 'first_name'];
    protected $guarded = [];
    public $timestamps = false;

    public function payment()
    {
        return $this->hasMany(Payment::class, 'user_id', 'vk_id');
    }

    public static function add($fields)
    {
        $user = new static;
        $user->fill($fields);
        $user->save();

        return $user;
    }
    
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

    public static function getLatest()
    {
        $users = User::orderBy('id', 'desc')->take(10)->get();
        return $users;
    }

    public static function getReferals($id)
    {
        $refs = User::where('ref', '=', $id)->get();
        return $refs;
    }

    public static function getLastUsers()
    {
        $last_users = User::where('last_seen', '=', date('Y-m-d'))->get();
        return $last_users;
    }

    public static function getById($id)
    {
        $user = User::where('vk_id', '=', $id)->first();
        return $user;
    }

    public static function getNewUsers()
    {
        $new_users = User::where('reg', '=', date('Y-m-d'))->get();
        return $new_users;
    }

    public static function withdrawBalance($id, $summ)
    {
        $user = User::where('vk_id', '=', $id)->first();
        if (($user->balance - $summ) > 0){

            $user->balance = $user->balance - $summ;

            return $user->balance;
        } 

        return 'Не хватает средств';


    }

}
