<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ImportError extends Model
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
        'import_job_id',
        'entity_type',
        'line_number',
        'raw_data',
        'error_message'
    ];

    public function importJob() {
        return $this->belongsTo(ImportJob::class);
    }
}
