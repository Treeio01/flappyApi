<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Giveaway extends Model
{
    use HasFactory;
    protected $fillable = ['name','network','description','image','active','winner_mode'];
    public function entries() { return $this->hasMany(Entry::class); }
}
