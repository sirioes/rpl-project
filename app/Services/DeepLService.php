<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepLService
{
    private string $apiKey;
    private string $baseUrl;

    // DeepL language codes for each app locale
    private array $localeMap = [
        'en' => 'EN-US',
        'id' => 'ID',
        'nl' => 'NL',
        'de' => 'DE',
        'pt' => 'PT-PT',
    ];

    public function __construct()
    {
        $this->apiKey  = config('services.deepl.key', '');
        $this->baseUrl = config('services.deepl.free', true)
            ? 'https://api-free.deepl.com/v2'
            : 'https://api.deepl.com/v2';
    }

    /**
     * Translate multiple fields to all 5 supported languages.
     * Returns: ['EN' => ['field' => 'text', ...], 'ID' => [...], ...]
     */
    public function translateAll(array $fields): array
    {
        if (empty($this->apiKey)) {
            Log::warning('DeepL API key not configured.');
            return [];
        }

        $texts  = array_values($fields);
        $keys   = array_keys($fields);
        $result = [];

        foreach ($this->localeMap as $locale => $deeplLang) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'DeepL-Auth-Key ' . $this->apiKey,
                ])->post($this->baseUrl . '/translate', [
                    'text'        => $texts,
                    'target_lang' => $deeplLang,
                ]);

                if ($response->successful()) {
                    $translated = collect($response->json('translations'))->pluck('text')->toArray();
                    $result[strtoupper($locale)] = array_combine($keys, $translated);
                } else {
                    Log::error('DeepL error', ['lang' => $deeplLang, 'status' => $response->status()]);
                    $result[strtoupper($locale)] = array_combine($keys, $texts);
                }
            } catch (\Exception $e) {
                Log::error('DeepL exception', ['lang' => $deeplLang, 'message' => $e->getMessage()]);
                $result[strtoupper($locale)] = array_combine($keys, $texts);
            }
        }

        return $result;
    }
}
