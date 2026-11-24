<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collaboration extends Model
{
    use HasFactory;

    protected $fillable = [
        'collaboratable_type',
        'collaboratable_id',
        'inviter_id',
        'collaborator_id',
        'status',
        'role_label',
    ];

    public function collaboratable()
    {
        return $this->morphTo();
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    public function collaborator()
    {
        return $this->belongsTo(User::class, 'collaborator_id');
    }
}
