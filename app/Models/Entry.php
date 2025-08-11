<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','giveaway_id','wallet','verified'];
    public function user() { return $this->belongsTo(User::class); }
    public function giveaway() { return $this->belongsTo(Giveaway::class); }
}
