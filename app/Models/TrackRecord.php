<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackRecord extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'translations' => 'array',
    ];

    public function items()
    {
        return $this->hasMany(TrackRecordItem::class);
    }

    public function translate(string $field): string
    {
        $localeMap = ['en' => 'EN', 'id' => 'ID', 'nl' => 'NL', 'de' => 'DE', 'pt' => 'PT'];
        $key       = $localeMap[app()->getLocale()] ?? 'EN';
        $trans     = $this->translations ?? [];

        return $trans[$key][$field] ?? $trans['EN'][$field] ?? $this->$field ?? '';
    }
}
