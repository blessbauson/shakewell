<?php

namespace App\Models;

use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Http\Request;

use Carbon\Carbon;

class User extends Authenticatable
{
    
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    public $table       = 'users';
    protected $with     = ['vouchers'];
    public $timestamps  = true;

    protected $dates = [
        'updated_at',
        'created_at',
        'deleted_at',
        'email_verified_at'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'username',
        'password',
        'first_name',
        'last_name',
        'email',
        'email_verified_at',
        'updated_at',
        'created_at',
        'deleted_at',
        'created_by',
        'updated_by',
        'deleted_by'
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'pivot'
    ];
    

    public function vouchers()
    {
        return $this->hasMany(Voucher::class, 'user_id', 'id')->select(['vouchers.id', 'vouchers.code', 'vouchers.user_id']);
    }


    //Fix for UTC format return on API Json responses
    public function serializeDate($date): ?string
    {
        return ($date != null) ?  $date->format('Y-m-d H:i:s') : null;
    }


    protected static function getSearchableFields(): array
    {
        return ['username', 'first_name', 'last_name', 'email'];
    }


    public function scopeListConditions($query, Request $request)
    {
        return $query->when(($request->has('search') && !empty($request->search)), function ($query) use ($request) {
                    $query->where('username', 'LIKE', '%'.$request->search.'%');
                    $query->orWhere('first_name', 'LIKE', '%'.$request->search.'%');
                    $query->orWhere('last_name', 'LIKE', '%'.$request->search.'%');
                    $query->orWhere('email', 'LIKE', '%'.$request->search.'%');
                })
                ->whereNull('deleted_at');
    }


    /**************************************** QUERIES ******************************************************/

    public function updatePassword($password)
    {
        $user = User::where('id',$this->id)->update(['password' => $password, 'password_changed_at' => Carbon::now()]);
        return $user;
    }
}
