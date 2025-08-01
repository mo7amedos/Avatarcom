<!DOCTYPE html>
<html {!! Theme::htmlAttributes() !!}>
<head>
    <meta charset="utf-8">
    <meta
        http-equiv="X-UA-Compatible"
        content="IE=edge"
    >
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
    <meta
        name="format-detection"
        content="telephone=no"
    >
    <meta
        name="apple-mobile-web-app-capable"
        content="yes"
    >

    @if ($favicon = theme_option('favicon'))
        {{ Html::favicon(
            RvMedia::getImageUrl($favicon),
            ['type' => rescue(fn () => RvMedia::getMimeType($favicon), 'image/x-icon')]
        ) }}
    @endif

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>{{ page_title()->getTitle(false) }}</title>

    <style>
        :root {
            --primary-font: '{{ theme_option('primary_font', 'Muli') }}', sans-serif;
            --primary-color: {{ theme_option('primary_color', '#fab528') }};
        }
    </style>

    @yield('header', view(MarketplaceHelper::viewPath('vendor-dashboard.layouts.header')))

    <script>
        window.siteUrl = "{{ BaseHelper::getHomepageUrl() }}";
        window.siteEditorLocale = "{{ apply_filters('cms_site_editor_locale', App::getLocale()) }}";
    </script>

    <script type="text/javascript">
        'use strict';
        window.trans = Object.assign(window.trans || {}, JSON.parse('{!! addslashes(json_encode(trans('plugins/marketplace::marketplace'))) !!}'));

        var BotbleVariables = BotbleVariables || {};
        BotbleVariables.languages = {
            tables: {!! json_encode(trans('core/base::tables'), JSON_HEX_APOS) !!},
            notices_msg: {!! json_encode(trans('core/base::notices'), JSON_HEX_APOS) !!},
            pagination: {!! json_encode(trans('pagination'), JSON_HEX_APOS) !!},
            system: {
                character_remain: '{{ trans('core/base::forms.character_remain') }}'
            }
        };

        var RV_MEDIA_URL = {
            'media_upload_from_editor': '{{ route('marketplace.vendor.upload-from-editor') }}'
        };
    </script>

    @stack('header')
</head>

<body @if (session('locale_direction', 'ltr') == 'rtl') dir="rtl" @endif>

@yield('body', view(MarketplaceHelper::viewPath('vendor-dashboard.layouts.body')))

@stack('pre-footer')

@if (session()->has('status') ||
        session()->has('success_msg') ||
        session()->has('error_msg') ||
        (isset($errors) && $errors->count() > 0) ||
        isset($error_msg))
    <script type="text/javascript">
        'use strict';
        window.noticeMessages = [];
        @if (session()->has('success_msg'))
        noticeMessages.push({
            'type': 'success',
            'message': "{!! addslashes(session('success_msg')) !!}"
        });
        @endif
        @if (session()->has('status'))
        noticeMessages.push({
            'type': 'success',
            'message': "{!! addslashes(session('status')) !!}"
        });
        @endif
        @if (session()->has('error_msg'))
        noticeMessages.push({
            'type': 'error',
            'message': "{!! addslashes(session('error_msg')) !!}"
        });
        @endif
        @if (isset($error_msg))
        noticeMessages.push({
            'type': 'error',
            'message': "{!! addslashes($error_msg) !!}"
        });
        @endif
        @if (isset($errors))
        @foreach ($errors->all() as $error)
        noticeMessages.push({
            'type': 'error',
            'message': "{!! addslashes($error) !!}"
        });
        @endforeach
        @endif
    </script>
@endif

{!! Assets::renderFooter() !!}
@yield('footer', view(MarketplaceHelper::viewPath('vendor-dashboard.layouts.footer')))

@stack('scripts')
@stack('footer')
{!! apply_filters(THEME_FRONT_FOOTER, null) !!}
</body>

</html>
