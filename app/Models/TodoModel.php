<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TodoModel extends Model
{
    protected $fillable = [
        'key',
        'name',
        'updated_at'
    ];
    protected $table = 'todo';
}
