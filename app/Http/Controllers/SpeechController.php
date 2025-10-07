<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SpeechController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'text' => 'required|string',
            'language' => 'required|string',
        ]);

        $text = $request->input('text');
        $targetLanguage = $request->input('language');
        $sourceLanguage = 'en'; // Assuming input text is always English

        // Ensure input text is valid UTF-8 before sending to MyMemory
        $text = iconv("UTF-8", "UTF-8//IGNORE", $text); // More aggressive sanitization

        $myMemoryApiKey = env('MYMEMORY_API_KEY');
        $voiceRSSApiKey = env('VOICERSS_API_KEY');

        if (!$myMemoryApiKey) {
            Log::error('MYMEMORY_API_KEY is not set in .env');
            return response()->json(['error' => 'Server configuration error: MyMemory API key missing.'], 500);
        }

        if (!$voiceRSSApiKey) {
            Log::error('VOICERSS_API_KEY is not set in .env');
            return response()->json(['error' => 'Server configuration error: VoiceRSS API key missing.'], 500);
        }

        try {
            // 1. Translate the text using MyMemory API
            $translationResponse = Http::get('http://api.mymemory.translated.net/get', [
                'q' => $text,
                'langpair' => $sourceLanguage . '|' . $targetLanguage,
                'key' => $myMemoryApiKey, // Optional, for higher limits
            ]);

            $rawMyMemoryResponse = $translationResponse->body();
            Log::info('MyMemory Raw Response:' . $rawMyMemoryResponse);

            $translationData = $translationResponse->json();

            if ($translationResponse->failed() || !isset($translationData['responseData']['translatedText'])) {
                Log::error('MyMemory Translation API Error: ' . ($translationData['responseDetails'] ?? 'Unknown error') . ' - Raw Response: ' . $rawMyMemoryResponse);
                return response()->json(['error' => 'Failed to translate text.'], 500);
            }

            $translatedText = $translationData['responseData']['translatedText'];

            // Aggressively sanitize translated text for VoiceRSS to ensure only valid UTF-8
            // Remove HTML tags and decode HTML entities, then sanitize UTF-8
            $translatedText = strip_tags($translatedText);
            $translatedText = html_entity_decode($translatedText, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $translatedText = iconv("UTF-8", "UTF-8//IGNORE", $translatedText);

            // Replace apostrophes with a space to prevent encoding issues with VoiceRSS
            $translatedText = str_replace('\'', ' ', $translatedText);

            Log::info('Translated Text before VoiceRSS:' . $translatedText);

            // 2. Synthesize the translated text into speech using VoiceRSS API
            // VoiceRSS uses specific language codes, we need to map them if different
            $voiceRssLangCode = $this->getVoiceRssLanguageCode($targetLanguage);

            $voiceRssParams = [
                'key' => $voiceRSSApiKey,
                'hl' => $voiceRssLangCode,
                'src' => $translatedText, // Let Http::get handle URL encoding
                'r' => 0, // Rate (0 = normal)
                'c' => 'MP3',
                'ssml' => false,
                'b64' => true, // Request base64 encoded audio
            ];

            Log::info('VoiceRSS Request URL:' . 'http://api.voicerss.org/?' . http_build_query($voiceRssParams));

            $voiceRssResponse = Http::get('http://api.voicerss.org/', $voiceRssParams);

            $rawVoiceRssResponse = $voiceRssResponse->body();
            // Log::info('VoiceRSS Raw Response:' . $rawVoiceRssResponse); // Removed, as it causes UTF-8 errors with binary data

            // VoiceRSS typically returns clear error messages as text, not binary MP3, if there's a problem.
            // If we receive binary data (starting with ID3), we can assume it's an MP3.
            // We'll rely on HTTP status codes and the presence of binary data for success/failure.
            if ($voiceRssResponse->failed() || empty($rawVoiceRssResponse) || !str_starts_with($rawVoiceRssResponse, 'ID3')) {
                Log::error('VoiceRSS TTS API Error: Invalid or empty response received.');
                return response()->json(['error' => 'Failed to generate speech: Invalid or empty audio received from VoiceRSS.'], 500);
            }

            // VoiceRSS returns raw MP3 binary data despite b64=true, so we need to base64 encode it.
            $audioContentBase64 = base64_encode($rawVoiceRssResponse);

            return response()->json(['audio' => $audioContentBase64, 'translated_text' => $translatedText]);

        } catch (\Exception $e) {
            Log::error('API Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate speech: ' . $e->getMessage()], 500);
        }
    }

    private function getVoiceRssLanguageCode(string $laravelLangCode): string
    {
        // Map Laravel's language codes to VoiceRSS's specific language codes
        $map = [
            'en' => 'en-us',
            'es' => 'es-es',
            'fr' => 'fr-fr',
            'de' => 'de-de',
            'it' => 'it-it',
            'ja' => 'ja-jp',
            'ar' => 'ar-sa', // Arabic (Saudi Arabia) - Common VoiceRSS code
            'hi' => 'hi-in', // Hindi (India) - Common VoiceRSS code
        ];

        return $map[$laravelLangCode] ?? 'en-us'; // Default to en-us if not found
    }
}
