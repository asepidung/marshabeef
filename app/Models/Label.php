<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Label extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'production_date',
        'type_id',
        'expired_date',
        'print_expired',
        'weight',
        'qty_pcs',
        'counter',
        'barcode',
    ];

    protected $casts = [
        'production_date' => 'date',
        'expired_date'    => 'date',
        'print_expired'   => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }
}
