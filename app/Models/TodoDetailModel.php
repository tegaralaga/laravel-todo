<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TodoDetailModel extends Model
{
    protected $fillable = [
        'todo_id',
        'item',
        'created_at',
    ];
    public $timestamps = false;
    protected $table = 'todo_detail';
}
