<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'code', 'is_active'];

    public function labels()
    {
        return $this->hasMany(Label::class);
    }

    public function hasTransactions(): bool
    {
        return $this->labels()->withoutTrashed()->exists();
    }
}
