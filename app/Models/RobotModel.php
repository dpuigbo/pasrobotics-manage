<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RobotModel extends Model
{
    protected $fillable = ['manufacturer','name','family','notes'];
}
