<?php

namespace VigStudio\VigAutoTranslations\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use VigStudio\VigAutoTranslations\Contracts\Translator;

/**
 * Use the AWS Translate service
 *
 * https://aws.amazon.com/translate/
 */
class ChatGPTTranslator implements Translator
{
    public function translate(string $source, string $target, string $value): string|null
    {
        $apiKey = setting('vig_translate_chatgpt_key', config('plugins.vig-auto-translations.general.chatgpt_key'));

        //Make a POST request to the OpenAI API
        $response = Http::withoutVerifying()
            ->asJson()
            ->withToken($apiKey)
            ->post('https://api.openai.com/v1/completions', [
                'model' => 'text-davinci-003',
                'prompt' => sprintf('translations "%s" to %s from %s', $value, $target, $source),
                'max_tokens' => 1000,
            ]);

        if ($response->failed()) {
            return $value;
        }

        $translated = (string)$response->json('choices.0.text');

        $translated = str_replace("$source: ", '', $translated);
        $translated = str_replace(ucfirst($source) . ': ', '', $translated);
        $translated = str_replace("<$source>", '', $translated);
        $translated = str_replace("</$source>", '', $translated);

        return Str::squish($translated ?: $value);
    }
}
