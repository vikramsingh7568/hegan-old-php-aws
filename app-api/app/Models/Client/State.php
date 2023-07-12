<?php

namespace App\Models\Client;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'states';

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
