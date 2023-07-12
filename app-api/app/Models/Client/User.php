<?php

namespace App\Models\Client;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'register_as',
        'plan_id',
        'first_name',
        'last_name',
        'email',
        'firm_name',
        'user_name',
        'mobile_no',
        'address',
        'pin_code',
        'dl_no_1',
        'dl_no_2',
        'gst_no',
        'fssai_no',
        'account_name',
        'account_no',
        'ifsc_code',
        'branch',
        'state_id',
        'signature',
        'password',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getSignatureAttribute($value)
    {
        if ($value) {
            // uploads/signatures
            return url('/public/uploads/' . $value);
        } else {
            return ''; //url('images/profile_images/unsplash.jpg');
        }
    }

    public function getAccountNameAttribute($value)
    {
        return is_null($value) ? '' : $value;
    }
    public function getAccountNoAttribute($value)
    {
        return is_null($value) ? '' : $value;
    }
    public function getIfscCodeAttribute($value)
    {
        return is_null($value) ? '' : $value;
    }

    public function getAddressAttribute($value)
    {
        return is_null($value) ? '' : $value;
    }
    public function getBranchAttribute($value)
    {
        return is_null($value) ? '' : $value;
    }
    public function getDlNo1Attribute($value)
    {
        return is_null($value) ? '' : $value;
    }
    public function getDlNo2Attribute($value)
    {
        return is_null($value) ? '' : $value;
    }

    public function getFssaiNoAttribute($value)
    {
        return is_null($value) ? '' : $value;
    }
    public function getGstNoAttribute($value)
    {
        return is_null($value) ? '' : $value;
    }


    /**
     * Change Date format. Format define at app config file.
     *
     * @var string
     * @return string
     */
    public function getCreatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format(config('app.date_time_format'));
    }

    public function getUpdatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format(config('app.date_time_format'));
    }
}
