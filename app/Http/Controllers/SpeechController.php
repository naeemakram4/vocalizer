<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Cloud\Translate\V2\TranslateClient;
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
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

        $apiKey = env('GOOGLE_CLOUD_API_KEY');

        if (!$apiKey) {
            Log::error('GOOGLE_CLOUD_API_KEY is not set in .env');
            return response()->json(['error' => 'Server configuration error: API key missing.'], 500);
        }

        try {
            // 1. Translate the text
            $translate = new TranslateClient(['key' => $apiKey]);
            $translation = $translate->translate($text, [
                'target' => $targetLanguage,
            ]);
            $translatedText = $translation['text'];

            // 2. Synthesize the translated text into speech
            $textToSpeechClient = new TextToSpeechClient(['key' => $apiKey]);
            $synthesisInput = (new SynthesisInput())->setText($translatedText);

            // Select the voice to be used
            // For simplicity, we'll use a default male voice if available for the language.
            // In a real app, you'd allow users to select from available voices.
            $voice = (new VoiceSelectionParams())
                ->setLanguageCode($targetLanguage)
                ->setSsmlGender(\Google\Cloud\TextToSpeech\V1\SsmlVoiceGender::MALE);

            // Configure the audio format
            $audioConfig = (new AudioConfig())
                ->setAudioEncoding(\Google\Cloud\TextToSpeech\V1\AudioEncoding::MP3);

            $response = $textToSpeechClient->synthesizeSpeech($synthesisInput, $voice, $audioConfig);
            $audioContent = $response->getAudioContent();

            $textToSpeechClient->close();

            return response()->json(['audio' => base64_encode($audioContent), 'translated_text' => $translatedText]);

        } catch (\Exception $e) {
            Log::error('API Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate speech: ' . $e->getMessage()], 500);
        }
    }
}
