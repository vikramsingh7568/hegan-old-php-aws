<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ApiExceptions Model.
 *
 * @description Store Api Error and exceptions
 *  
 *
 * @author Pa1 <dropmail2pavan@gmail.com>
 */

class ApiExceptions extends Model
{
    public $timestamps = true;
	protected $table = 'api_exceptions';
	protected $guarded = [];

	protected function serializeDate(DateTimeInterface $date) {
        return $date->format('d-m-Y H:i:s');
    }
}
