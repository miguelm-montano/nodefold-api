<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    use HasFactory;
    
    protected $fillable = ['user_id', 'parent_id', 'name'];

    protected $hidden = ['user_id', 'created_at', 'updated_at'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function parent() {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function folders() {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function resources() {
        return $this->hasMany(Resource::class);
    }
}
