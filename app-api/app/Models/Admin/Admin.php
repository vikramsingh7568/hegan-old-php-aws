<?php

namespace App\Models\Admin;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
  use HasApiTokens, HasFactory, Notifiable;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',
    'email',
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

  public function getProfileImageAttribute($value)
  {
    if ($value) {
      return url('images/profile_images/' . $value);
    } else {
      return url('images/profile_images/unsplash.jpg');
    }
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
