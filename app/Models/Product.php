<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_name',
        'product_description',
        'product_price',
        'ticket_quota',
        'departure_date',
        'departure_locations',
        'product_image',
        'is_published',
        'translations',
        'whatsapp_link',
    ];

    protected $casts = [
        'product_price'    => 'decimal:2',
        'ticket_quota'     => 'integer',
        'departure_date'   => 'datetime',
        'product_image'    => 'array',
        'is_published'     => 'boolean',
        'translations'     => 'array',
    ];

    public function isExpired(): bool
    {
        return $this->departure_date !== null && $this->departure_date->isPast();
    }

    public function translate(string $field): string
    {
        $localeMap = ['en' => 'EN', 'id' => 'ID', 'nl' => 'NL', 'de' => 'DE', 'pt' => 'PT'];
        $key       = $localeMap[app()->getLocale()] ?? 'EN';
        $trans     = $this->translations ?? [];

        return $trans[$key][$field] ?? $trans['EN'][$field] ?? $this->$field ?? '';
    }
}
