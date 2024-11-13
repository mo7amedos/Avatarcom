<?php

namespace VigStudio\VigAutoTranslations\Http\Requests;

use Botble\Support\Http\Requests\Request;

class SettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'vig_translate_driver' => ['required'],
            'vig_translate_aws_key' => ['required_if:vig_translate_driver,aws'],
            'vig_translate_aws_secret' => ['required_if:vig_translate_driver,aws'],
            'vig_translate_aws_region' => ['required_if:vig_translate_driver,aws'],
            'vig_translate_chatgpt_key' => ['required_if:vig_translate_driver,chatgpt'],
        ];
    }
}
