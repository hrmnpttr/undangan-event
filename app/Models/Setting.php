<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'value',
    ];

    public static function getValue(string $nama, $default = null): ?string
    {
        return static::where('nama', $nama)->value('value') ?? $default;
    }

    public static function setValue(string $nama, ?string $value): void
    {
        static::updateOrCreate(['nama' => $nama], ['value' => $value]);
    }
}
