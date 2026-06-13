<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackRecordItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'translations' => 'array',
    ];

    public function trackRecord()
    {
        return $this->belongsTo(TrackRecord::class);
    }

    public function translate(string $field): string
    {
        $localeMap = ['en' => 'EN', 'id' => 'ID', 'nl' => 'NL', 'de' => 'DE', 'pt' => 'PT'];
        $key       = $localeMap[app()->getLocale()] ?? 'EN';
        $trans     = $this->translations ?? [];

        return $trans[$key][$field] ?? $trans['EN'][$field] ?? $this->$field ?? '';
    }
}
