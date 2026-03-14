<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'record_accessed',
        'accessed_at',
        'ip_address',
    ];

    public $timestamps = false; // We use accessed_at, and standard created_at/updated_at are manually disabled if unnecessary, but let's just keep timestamps true and standard since migration has them.

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
