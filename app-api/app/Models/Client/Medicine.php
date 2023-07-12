<?php

namespace App\Models\Client;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'products';

    public function getProductNameAttribute($value)
    {
        return $value ? ucfirst($value) : '';
    }
    public function getBatchNoAttribute($value)
    {
        return is_null($value) ? '' : $value;
    }
    public function getUnitPackingAttribute($value)
    {
        return is_null($value) ? '' : $value;
    }
    public function getMinQtyAttribute($value)
    {
        return is_null($value) ? '' : $value;
    }
    public function getMinQtyDiscountAttribute($value)
    {
        return is_null($value) ? '' : $value;
    }
    public function getMinQtyBonusDealAttribute($value)
    {
        return is_null($value) ? '' : $value;
    }

    public function getMinQtyNetRateAttribute($value)
    {
        return is_null($value) ? '' : $value;
    }
    public function getMinQtyTradeRateAttribute($value)
    {
        return is_null($value) ? '' : $value;
    }
    public function getMinQtyMarginPercentageAttribute($value)
    {
        return is_null($value) ? '' : $value;
    }

    public function getProductImageAttribute($value)
    {
        if ($value) {
            // public uploads/signatures
            return url('public/uploads/' . $value);
        } else {
            return url('public/images/no-image.jpg');
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
