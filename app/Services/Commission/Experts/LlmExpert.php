<?php

namespace App\Services\Commission\Experts;

use App\Services\PromptBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LlmExpert
{
    public string $name;
    protected array $config;
    protected array $extra;
    protected  bool $selfReview;

    public function __construct(string $name, array $config, bool $selfReview)
    {
        $this->name = $name;
        $this->config = $config;
        $this->extra = $config['extra'] ?? [];
        $this->selfReview = $selfReview;
    }
    public function generate(string $code, string $focus = 'full'): array
    {
        $prompt = PromptBuilder::forCodeAnalysis($code, $this->extra['prompt_suffix'] ?? '', $focus);
        $original = $this->sendPrompt($prompt);

        if ($this->selfReview && is_array($original) && !empty($original)) {
            $reviewPrompt = PromptBuilder::forSelfReview($code, $original);
            $additions = $this->sendPrompt($reviewPrompt);

            if (is_array($additions) && !empty($additions)) {
                $original = array_merge($original, $additions);
            }
        }

        return $original;
    }

    public function generateFromPrompt(string $prompt): ?string
    {
        $url = str_replace('{key}', $this->config['key'], $this->extra['url']);
        $headers = $this->buildHeaders();
        $payload = $this->buildPayload($prompt);

        $response = Http::withHeaders($headers)->post($url, $payload);

        if (!$response->successful()) {
            logger()->error("{$this->name} falló al generar contenido libre desde prompt", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        $raw = Arr::get($response->json(), $this->extra['response_path'] ?? '');

        return is_string($raw) && trim($raw) !== '' ? $raw : null;
    }

    protected function sendPrompt(string $prompt): array
    {
        $url = str_replace('{key}', $this->config['key'], $this->extra['url']);
        $headers = $this->buildHeaders();
        $payload = $this->buildPayload($prompt);

        $response = Http::withHeaders($headers)->post($url, $payload);

        if (!$response->successful()) {
            logger()->error("{$this->name} falló al enviar prompt", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return [];
        }

        $raw = Arr::get($response->json(), $this->extra['response_path'] ?? '');
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }
    protected function buildHeaders(): array
    {
        $headers = $this->extra['headers'] ?? [];

        foreach ($headers as $key => $value) {
            $headers[$key] = str_replace('{key}', $this->config['key'], $value);
        }

        return $headers;
    }

    protected function buildPayload(string $prompt): array
    {
        if (isset($this->extra['payload'])) {
            $payloadTemplate = $this->extra['payload'];
        } else {
            // fallback  OpenAI like
            $payloadTemplate = [
                'model' => $this->config['model'],
                'messages' => $this->extra['messages'] ?? [],
            ];
        }
        $payload = $this->deepReplace($payloadTemplate, '{prompt}', $prompt);
        if (isset($this->extra['model_key'])) {
            $payload[$this->extra['model_key']] = $this->config['model'];
        }
        if (isset($this->extra['temperature'])) {
            $payload['temperature'] = $this->extra['temperature'];
        }

        return $payload;
    }

    protected function deepReplace(array $data, string $needle, string $replacement): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->deepReplace($value, $needle, $replacement);
            } elseif (is_string($value)) {
                $data[$key] = str_replace($needle, $replacement, $value);
            }
        }

        return $data;
    }

    public function normalizeFromPrompt(string $prompt): array
    {
        $url = str_replace('{key}', $this->config['key'], $this->extra['url']);
        $headers = $this->buildHeaders();
        $payload = $this->buildPayload($prompt);

        $response = Http::withHeaders($headers)->post($url, $payload);

        if (!$response->successful()) {
            logger()->error("Error al normalizar matriz con {$this->name}", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return [];
        }

        $raw = Arr::get($response->json(), $this->extra['response_path'] ?? '');
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }


}
