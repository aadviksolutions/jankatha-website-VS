<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NewsAiProcessorService
{
    /**
     * Determine if AI processing is configured via environment variables.
     */
    public function isConfigured(): bool
    {
        return ! empty(env('GEMINI_API_KEY')) || ! empty(env('OPENAI_API_KEY'));
    }

    /**
     * Process an incoming news item with AI if configured.
     * Never invents facts or fabricates details.
     * Gracefully returns original data if AI is not configured or fails.
     *
     * @param  array{headline: string, summary: ?string, content: string, category: ?string, district: ?string}  $data
     * @return array{headline: string, summary: ?string, content: string, category: ?string, district: ?string, tags: ?string}
     */
    public function process(array $data): array
    {
        if (! $this->isConfigured()) {
            return array_merge([
                'tags' => null,
            ], $data);
        }

        try {
            if (! empty(env('GEMINI_API_KEY'))) {
                return $this->processWithGemini($data);
            }

            if (! empty(env('OPENAI_API_KEY'))) {
                return $this->processWithOpenAi($data);
            }
        } catch (\Throwable $e) {
            Log::warning('AI news processing failed, falling back to original content: '.$e->getMessage());
        }

        return array_merge([
            'tags' => null,
        ], $data);
    }

    /**
     * Call Gemini API to clean up headline and generate brief summary.
     */
    protected function processWithGemini(array $data): array
    {
        $apiKey = env('GEMINI_API_KEY');
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key='.$apiKey;

        $prompt = "You are a professional Hindi/English news editor for Jankatha.com. Clean up this news item. DO NOT invent facts, names, dates, quotes or locations. Return ONLY a valid JSON object with keys: 'headline' (concise clear title), 'summary' (accurate 1-2 sentence neutral summary), 'tags' (comma-separated 3-5 relevant keywords).\n\nOriginal Headline: {$data['headline']}\nOriginal Text: ".Str::limit(strip_tags($data['content']), 800);

        $response = Http::timeout(8)->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
            ],
        ]);

        if ($response->successful()) {
            $json = $response->json();
            $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
            if ($text) {
                $parsed = json_decode($text, true);
                if (is_array($parsed)) {
                    return [
                        'headline' => ! empty($parsed['headline']) ? trim($parsed['headline']) : $data['headline'],
                        'summary' => ! empty($parsed['summary']) ? trim($parsed['summary']) : $data['summary'],
                        'content' => $data['content'],
                        'category' => $data['category'],
                        'district' => $data['district'],
                        'tags' => ! empty($parsed['tags']) ? trim($parsed['tags']) : null,
                    ];
                }
            }
        }

        return array_merge(['tags' => null], $data);
    }

    /**
     * Call OpenAI API to clean up headline and generate brief summary.
     */
    protected function processWithOpenAi(array $data): array
    {
        $apiKey = env('OPENAI_API_KEY');
        $url = 'https://api.openai.com/v1/chat/completions';

        $prompt = "You are a news editor for Jankatha.com. Clean up this news item. DO NOT invent facts, names, dates, quotes or locations. Return a JSON object with: 'headline' (concise clean title), 'summary' (1-2 sentence neutral summary), 'tags' (comma-separated keywords).\n\nOriginal Headline: {$data['headline']}\nOriginal Text: ".Str::limit(strip_tags($data['content']), 800);

        $response = Http::withToken($apiKey)->timeout(8)->post($url, [
            'model' => 'gpt-4o-mini',
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        if ($response->successful()) {
            $json = $response->json();
            $content = $json['choices'][0]['message']['content'] ?? null;
            if ($content) {
                $parsed = json_decode($content, true);
                if (is_array($parsed)) {
                    return [
                        'headline' => ! empty($parsed['headline']) ? trim($parsed['headline']) : $data['headline'],
                        'summary' => ! empty($parsed['summary']) ? trim($parsed['summary']) : $data['summary'],
                        'content' => $data['content'],
                        'category' => $data['category'],
                        'district' => $data['district'],
                        'tags' => ! empty($parsed['tags']) ? trim($parsed['tags']) : null,
                    ];
                }
            }
        }

        return array_merge(['tags' => null], $data);
    }
}
