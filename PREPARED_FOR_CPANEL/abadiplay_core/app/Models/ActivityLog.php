<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
        'model_type',
        'model_id',
    ];

    /**
     * User sing nglakoni aksi iki.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Simpan log aktivitas - Ben kabeh kejadian ke-catet rapi.
     */
    public static function log(string $action, string $description, ?string $modelType = null, ?int $modelId = null): static
    {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'model_type' => $modelType,
            'model_id' => $modelId,
        ]);
    }
}
