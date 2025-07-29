<?php

namespace VigStudio\VigAutoTranslations\Forms\Settings;

use Botble\Base\Forms\FieldOptions\RadioFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\RadioField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Setting\Forms\SettingForm;
use VigStudio\VigAutoTranslations\Http\Requests\SettingRequest;

class AutoTranslateSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setSectionTitle(trans('plugins/vig-auto-translations::vig-auto-translations.title'))
            ->setSectionDescription(trans('plugins/vig-auto-translations::vig-auto-translations.description'))
            ->setValidatorClass(SettingRequest::class)
            ->add(
                'vig_translate_driver',
                RadioField::class,
                RadioFieldOption::make()
                    ->label(trans('plugins/vig-auto-translations::vig-auto-translations.setting_driver'))
                    ->choices([
                        'google' => trans('plugins/vig-auto-translations::vig-auto-translations.google'),
                        'aws' => trans('plugins/vig-auto-translations::vig-auto-translations.aws'),
                        'chatgpt' => trans('plugins/vig-auto-translations::vig-auto-translations.chatgpt'),
                    ])
                    ->selected(setting('vig_translate_driver', 'google'))
                    ->toArray()
            )
            ->add(
                'vig_translate_aws_key',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/vig-auto-translations::vig-auto-translations.aws_key'))
                    ->maxLength(120)
                    ->allowOverLimit()
                    ->value(old('vig_translate_aws_key', setting('vig_translate_aws_key', config('plugins.vig-auto-translations.general.aws_key'))))
                    ->toArray()
            )
            ->add(
                'vig_translate_aws_secret',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/vig-auto-translations::vig-auto-translations.aws_secret'))
                    ->maxLength(120)
                    ->allowOverLimit()
                    ->value(old('vig_translate_aws_secret', setting('vig_translate_aws_secret', config('plugins.vig-auto-translations.general.aws_secret'))))
                    ->toArray()
            )
            ->add(
                'vig_translate_aws_region',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/vig-auto-translations::vig-auto-translations.aws_region'))
                    ->maxLength(120)
                    ->allowOverLimit()
                    ->value(old('vig_translate_aws_region', setting('vig_translate_aws_region', config('plugins.vig-auto-translations.general.aws_region'))))
                    ->toArray()
            )
            ->add(
                'vig_translate_chatgpt_key',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/vig-auto-translations::vig-auto-translations.chatgpt_key'))
                    ->maxLength(120)
                    ->allowOverLimit()
                    ->value(old('vig_translate_chatgpt_key', setting('vig_translate_chatgpt_key', config('plugins.vig-auto-translations.general.chatgpt_key'))))
                    ->toArray()
            );
    }
}
