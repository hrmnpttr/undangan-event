<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactPerson extends Model
{
    use HasFactory;

    protected $table = 'contact_persons';

    protected $fillable = [
        'nama',
        'telepon',
        'vvip',
    ];

    protected $casts = [
        'vvip' => 'boolean',
    ];
}
