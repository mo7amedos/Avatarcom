<script>
    console.log('testddđ')
</script>
{{-- <div class="flexbox-annotated-section">
    <div class="flexbox-annotated-section-annotation">
        <div class="annotated-section-title pd-all-20">
            <h2>{{ trans('plugins/vig-auto-translations::vig-auto-translations.title') }}</h2>
        </div>
        <div class="annotated-section-description pd-all-20 p-none-t">
            <p class="color-note">{{ trans('plugins/vig-auto-translations::vig-auto-translations.description') }}</p>
        </div>
    </div>

    <div class="flexbox-annotated-section-content">
        <div class="wrapper-content pd-all-20">
            <div class="form-group mb-3">
                <label class="text-title-field" for="vig_translate_driver">{{ trans('plugins/vig-auto-translations::vig-auto-translations.setting_driver') }}</label>

                <label class="me-2">
                    <input type="radio"
                           name="vig_translate_driver"
                           value="google"
                           @if (setting('vig_translate_driver') == 'google' || empty(setting('vig_translate_driver'))) checked @endif
                           class="setting-select-options">{{ trans('plugins/vig-auto-translations::vig-auto-translations.google') }}
                </label>
                <label class="me-2">
                    <input type="radio"
                           name="vig_translate_driver"
                           value="aws"
                           @if (setting('vig_translate_driver') == 'aws') checked @endif
                           class="setting-select-options" data-target="#vig-translate-aws-settings">{{ trans('plugins/vig-auto-translations::vig-auto-translations.aws') }}
                </label>
                <label class="me-2">
                    <input type="radio"
                           name="vig_translate_driver"
                           value="chatgpt"
                           @if (setting('vig_translate_driver') == 'chatgpt') checked @endif
                           class="setting-select-options" data-target="#vig-translate-chatgpt-settings">{{ trans('plugins/vig-auto-translations::vig-auto-translations.chatgpt') }}
                </label>
            </div>

            <div id="vig-translate-aws-settings" data-type="aws" @class([
                'mb-4 border rounded-top rounded-bottom p-3 bg-light setting-wrapper',
                'hidden' => setting('vig_translate_driver') != 'aws',
            ])>

                <div class="form-group mb-3">
                    <label class="text-title-field" for="vig_translate_aws_key">{{ trans('plugins/vig-auto-translations::vig-auto-translations.aws_key') }}</label>
                    <input data-counter="120"
                           type="text"
                           class="next-input"
                           name="vig_translate_aws_key"
                           id="vig_translate_aws_key"
                           value="{{ setting('vig_translate_aws_key', config('plugins.vig-auto-translations.general.aws_key')) }}" placeholder="{{ trans('plugins/vig-auto-translations::vig-auto-translations.aws_key') }}">
                </div>

                <div class="form-group mb-3">
                    <label class="text-title-field" for="vig_translate_aws_secret">{{ trans('plugins/vig-auto-translations::vig-auto-translations.aws_secret') }}</label>
                    <input data-counter="120"
                           type="text"
                           class="next-input"
                           name="vig_translate_aws_secret"
                           id="vig_translate_aws_secret"
                           value="{{ setting('vig_translate_aws_secret', config('plugins.vig-auto-translations.general.aws_secret')) }}" placeholder="{{ trans('plugins/vig-auto-translations::vig-auto-translations.aws_secret') }}">
                </div>

                <div class="form-group mb-3">
                    <label class="text-title-field" for="vig_translate_aws_region">{{ trans('plugins/vig-auto-translations::vig-auto-translations.aws_region') }}</label>
                    <input data-counter="120"
                           type="text"
                           class="next-input"
                           name="vig_translate_aws_region"
                           id="vig_translate_aws_region"
                           value="{{ setting('vig_translate_aws_region', config('plugins.vig-auto-translations.general.aws_region')) }}" placeholder="{{ trans('plugins/vig-auto-translations::vig-auto-translations.aws_region') }}">
                </div>
            </div>

            <div id="vig-translate-chatgpt-settings" data-type="chatgpt" @class([
                'mb-4 border rounded-top rounded-bottom p-3 bg-light setting-wrapper',
                'hidden' => setting('vig_translate_driver') != 'chatgpt',
            ])>

                <div class="form-group mb-3">
                    <label class="text-title-field" for="vig_translate_chatgpt_key">{{ trans('plugins/vig-auto-translations::vig-auto-translations.chatgpt_key') }}</label>
                    <input data-counter="120"
                           type="text"
                           class="next-input"
                           name="vig_translate_chatgpt_key"
                           id="vig_translate_chatgpt_key"
                           value="{{ setting('vig_translate_chatgpt_key', config('plugins.vig-auto-translations.general.chatgpt_key')) }}" placeholder="{{ trans('plugins/vig-auto-translations::vig-auto-translations.chatgpt_key') }}">
                </div>

            </div>
        </div>
    </div>
</div> --}}
