<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ImportJob extends Model
{
    use HasFactory;

    protected static function boot() {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'filename',
        'entity_type',
        'status',
        'total',
        'success',
        'failed'
    ];
    
    public function errors() {
        return $this->hasMany(ImportError::class);
    }

    public function incrementSuccess() {
        $this->increment('success');
        $this->increment('total');
    }

    public function incrementFailed() {
        $this->increment('failed');
        $this->increment('total');
    }
}
