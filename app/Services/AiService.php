<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class AiService
{
    public function __construct(
        public ?string $baseUrl = null,
        public ?string $apiKey = null,
        public ?string $model = null,
    ) {
        $this->baseUrl = $baseUrl ?: (string) site_setting('ai_base_url');
        $this->apiKey  = $apiKey  ?: (string) site_setting('ai_api_key');
        $this->model   = $model   ?: (string) site_setting('ai_model', 'gpt-4o-mini');
    }

    public static function isEnabled(): bool
    {
        return site_setting('ai_enabled') === '1'
            && site_setting('ai_base_url')
            && site_setting('ai_api_key')
            && site_setting('ai_model');
    }

    /** Detect whether the configured base URL points to Google Gemini. */
    public function isGemini(): bool
    {
        return str_contains(strtolower((string) $this->baseUrl), 'generativelanguage.googleapis.com');
    }

    /**
     * Send a chat request and return the generated text.
     * Auto-detects between Google Gemini and OpenAI-compatible providers.
     *
     * @param array<int, array{role:string, content:string}> $messages
     */
    public function chat(array $messages, int $maxTokens = 2048, float $temperature = 0.7, int $timeout = 60): string
    {
        if (!$this->baseUrl || !$this->apiKey || !$this->model) {
            throw new RuntimeException('AI لم يتم إعداده. الرجاء ضبط Base URL ومفتاح API والنموذج من إعدادات الموقع.');
        }

        return $this->isGemini()
            ? $this->geminiChat($messages, $maxTokens, $temperature, $timeout)
            : $this->openAiChat($messages, $maxTokens, $temperature, $timeout);
    }

    private function openAiChat(array $messages, int $maxTokens, float $temperature, int $timeout): string
    {
        $base = rtrim($this->baseUrl, '/');
        $endpoint = str_ends_with($base, '/chat/completions') ? $base : $base.'/chat/completions';

        $resp = Http::withToken($this->apiKey)
            ->acceptJson()
            ->timeout($timeout)
            ->post($endpoint, [
                'model'       => $this->model,
                'messages'    => $messages,
                'max_tokens'  => $maxTokens,
                'temperature' => $temperature,
            ]);

        if (!$resp->successful()) {
            throw new RuntimeException(
                'AI Error ('.$resp->status().'): '.data_get($resp->json(), 'error.message', $resp->body())
            );
        }

        return (string) data_get($resp->json(), 'choices.0.message.content', '');
    }

    private function geminiChat(array $messages, int $maxTokens, float $temperature, int $timeout): string
    {
        $base = rtrim($this->baseUrl, '/');
        // Allow base like https://generativelanguage.googleapis.com/v1beta or .../v1
        if (!preg_match('#/v1(?:beta)?$#', $base)) {
            $base = preg_replace('#/+$#', '', $base);
            if (!str_contains($base, '/v1')) {
                $base .= '/v1beta';
            }
        }

        $endpoint = $base.'/models/'.$this->model.':generateContent';

        // Convert OpenAI-style messages to Gemini "contents"
        $systemText = '';
        $contents = [];
        foreach ($messages as $m) {
            $role = $m['role'] ?? 'user';
            $text = (string) ($m['content'] ?? '');
            if ($role === 'system') {
                $systemText .= ($systemText ? "\n\n" : '').$text;
                continue;
            }
            $contents[] = [
                'role'  => $role === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $text]],
            ];
        }

        $payload = [
            'contents'         => $contents,
            'generationConfig' => [
                'temperature'     => $temperature,
                'maxOutputTokens' => $maxTokens,
            ],
        ];
        if ($systemText !== '') {
            $payload['systemInstruction'] = ['parts' => [['text' => $systemText]]];
        }

        $resp = Http::withHeaders(['X-goog-api-key' => $this->apiKey])
            ->acceptJson()
            ->timeout($timeout)
            ->post($endpoint, $payload);

        if (!$resp->successful()) {
            throw new RuntimeException(
                'Gemini Error ('.$resp->status().'): '.data_get($resp->json(), 'error.message', $resp->body())
            );
        }

        $parts = data_get($resp->json(), 'candidates.0.content.parts', []);
        $out = '';
        foreach ((array) $parts as $p) {
            $out .= (string) ($p['text'] ?? '');
        }
        return $out;
    }
}
