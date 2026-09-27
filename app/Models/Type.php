<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    protected $fillable = ['name', 'expired_in_days', 'is_active'];

    public function labels()
    {
        return $this->hasMany(Label::class);
    }

    public function hasTransactions(): bool
    {
        return $this->labels()->withoutTrashed()->exists();
    }
}
