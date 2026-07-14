<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = ['residential_name', 'nit', 'address', 'phone', 'email'];

    protected $attributes = ['response_days' => 15];

    protected function casts(): array
    {
        return ['response_days' => 'integer'];
    }

    public static function current(): self
    {
        return static::firstOrCreate([], [
            'residential_name' => 'Conjunto Residencial',
            'response_days' => 15,
        ]);
    }
}
