@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
    {{-- <div class="widget meta-boxes">
        <div class="widget-title">
            <h4>&nbsp; {{ trans('plugins/vig-auto-translations::vig-auto-translations.title') }}</h4>
        </div>
        <div class="widget-body box-translation" v-pre>
            @if (count(\Botble\Base\Supports\Language::getAvailableLocales()) > 0 && $group)
                <div class="row">
                    <div class="col-md-6">

                        <p>{{ trans('plugins/translation::translation.translate_from') }} <strong class="text-info">{{ $defaultLanguage ? $defaultLanguage['name'] : 'en' }}</strong> {{ trans('plugins/translation::translation.to') }} <strong class="text-info">{{ $group['name'] }}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <div class="text-end">
                            @include('plugins/vig-auto-translations::partials.list-theme-languages-to-translate', compact('groups', 'group'))
                        </div>
                    </div>
                </div>

                <p class="note note-warning">{{ trans('plugins/translation::translation.theme_translations_instruction') }}</p>
                @if ($group['locale'] != 'en')
                    <form action="{{ route('vig-auto-translations.theme.post-all') }}" method="POST" class="mb-3">
                        @csrf
                        <input type="hidden" name="locale" value="{{ $group['locale'] }}">
                        <button class="btn btn-info btn-begin-translate-all" type="button">
                            {{ trans('plugins/vig-auto-translations::vig-auto-translations.translate_all', ['language' => $group['name']]) }}
                        </button>
                    </form>
                @else
                    <p class="note note-danger">{{ trans('plugins/vig-auto-translations::vig-auto-translations.not_eng') }}</p>
                @endif
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ $defaultLanguage ? $defaultLanguage['name'] : 'en' }}</th>
                                <th>{{ $group['name'] }}</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($translations as $key => $translation)
                                <tr>
                                    <td class="text-start" style="width: 40%">
                                        {!! htmlentities($key, ENT_QUOTES, 'UTF-8', false) !!}
                                    </td>
                                    <td class="text-start" style="width: 40%">
                                        <a href="#" class="editable"
                                           data-name="{{ $key }}"
                                           data-type="textarea" data-pk="{{ $group['locale'] }}" data-url="{{ route('vig-auto-translations.theme') }}"
                                           data-title="{{ trans('plugins/translation::translation.edit_title') }}">{!! htmlentities($translation, ENT_QUOTES, 'UTF-8', false) !!}</a>
                                    </td>
                                    <td style="width: 20%">
                                        <div class="d-flex flex-row">
                                            @if ($group['locale'] !== 'en')
                                                @if ($key === $translation)
                                                    <button class="btn btn-primary" id="btn-vig-translate-auto" data-name="{{ $key }}" data-reset="0" type="button" title="{{ trans('plugins/vig-auto-translations::vig-auto-translations.translate') }}">
                                                        <i class="fa-sharp fa-solid fa-language"></i> {{ trans('plugins/vig-auto-translations::vig-auto-translations.translate') }}
                                                    </button>
                                                @else
                                                    <button class="btn btn-warning" id="btn-vig-translate-auto" data-name="{{ $key }}" data-reset="1" type="button" title="{{ trans('plugins/vig-auto-translations::vig-auto-translations.reset') }}">
                                                        <i class="fa-solid fa-rotate-left"></i> {{ trans('plugins/vig-auto-translations::vig-auto-translations.reset') }}
                                                    </button>
                                                @endif
                                            @endif
                                        </div>

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-warning">{{ trans('plugins/translation::translation.no_other_languages') }}</p>
            @endif
        </div>
        <div class="clearfix"></div>
    </div> --}}


    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{ trans('plugins/vig-auto-translations::vig-auto-translations.title') }}</h4>
                    </div>
                    <div class="card-body">
                        @if (count(\Botble\Base\Supports\Language::getAvailableLocales()) > 0 && $group)
                            <div class="row">
                                <div class="col-md-6">
                                    <p>{{ trans('plugins/translation::translation.translate_from') }} <strong class="text-info">{{ $defaultLanguage ? $defaultLanguage['name'] : 'en' }}</strong> {{ trans('plugins/translation::translation.to') }} <strong class="text-info">{{ $group['name'] }}</strong></p>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-end">
                                        @include('plugins/vig-auto-translations::partials.list-theme-languages-to-translate', compact('groups', 'group'))
                                    </div>
                                </div>
                            </div>

                            @if ($group['locale'] != 'en')
                                <form action="{{ route('vig-auto-translations.theme.post-all') }}" method="POST" class="mb-3">
                                    @csrf
                                    <input type="hidden" name="locale" value="{{ $group['locale'] }}">
                                    <button class="btn btn-info btn-begin-translate-all" type="button">
                                        {{ trans('plugins/vig-auto-translations::vig-auto-translations.translate_all', ['language' => $group['name']]) }}
                                    </button>
                                </form>
                            @else
                                <div class="alert alert-danger" role="alert">
                                    {{ trans('plugins/vig-auto-translations::vig-auto-translations.not_eng') }}
                                </div>
                            @endif
                            {{--
                            <div class="mb-3">
                                <label class="form-label">Buttons group with dropdown</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="btn-radio-dropdown" id="btn-radio-dropdown-1" autocomplete="off" checked="">
                                    <label for="btn-radio-dropdown-1" type="button" class="btn">Option 1</label>
                                    <input type="radio" class="btn-check" name="btn-radio-dropdown" id="btn-radio-dropdown-2" autocomplete="off">
                                    <label for="btn-radio-dropdown-2" type="button" class="btn">Option 2</label>
                                </div>
                            </div> --}}

                            <div class="table-responsive">
                                <table class="table table-vcenter card-table table-striped">
                                    <thead>
                                        <tr>
                                            <th>{{ $defaultLanguage ? $defaultLanguage['name'] : 'en' }}</th>
                                            <th>{{ $group['name'] }}</th>
                                            <th class="w-1"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($translations as $key => $translation)
                                            <tr>
                                                <td>
                                                    <code>{!! htmlentities($key, ENT_QUOTES, 'UTF-8', false) !!}</code>
                                                </td>
                                                <td>
                                                    <a href="#" class="editable"
                                                       data-name="{{ $key }}"
                                                       data-type="textarea" data-pk="{{ $group['locale'] }}" data-url="{{ route('vig-auto-translations.theme') }}"
                                                       data-title="{{ trans('plugins/translation::translation.edit_title') }}">{!! htmlentities($translation, ENT_QUOTES, 'UTF-8', false) !!}</a>
                                                </td>
                                                <td style="width: 20%">
                                                    <div class="d-flex flex-row">
                                                        @if ($group['locale'] !== 'en')
                                                            @if ($key === $translation)
                                                                <button class="btn btn-primary" id="btn-vig-translate-auto" data-name="{{ $key }}" data-reset="0" type="button" title="{{ trans('plugins/vig-auto-translations::vig-auto-translations.translate') }}">
                                                                    <i class="fa-sharp fa-solid fa-language"></i> {{ trans('plugins/vig-auto-translations::vig-auto-translations.translate') }}
                                                                </button>
                                                            @else
                                                                <button class="btn btn-warning" id="btn-vig-translate-auto" data-name="{{ $key }}" data-reset="1" type="button" title="{{ trans('plugins/vig-auto-translations::vig-auto-translations.reset') }}">
                                                                    <i class="fa-solid fa-rotate-left"></i> {{ trans('plugins/vig-auto-translations::vig-auto-translations.reset') }}
                                                                </button>
                                                            @endif
                                                        @endif
                                                    </div>

                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-warning">{{ trans('plugins/translation::translation.no_other_languages') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('footer')
    <script>
        $(document).on('click', '#btn-vig-translate-auto', function(event) {
            event.preventDefault();
            event.stopPropagation();
            $(this).prop('disabled', true).addClass('button-loading');
            var name = $(this).data('name');
            var reset = $(this).data('reset');

            $.ajax({
                type: 'POST',
                url: "{{ route('vig-auto-translations.theme') }}",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'pk': "{{ $group['locale'] }}",
                    'auto': !reset,
                    'value': name,
                    'name': name
                },
                success: res => {
                    if (!res.error) {
                        Botble.showSuccess(res.message);
                    } else {
                        Botble.showError(res.message);
                    }
                    location.reload();
                    $(this).prop('disabled', false).removeClass('button-loading');
                },
                error: res => {
                    $(this).prop('disabled', false).removeClass('button-loading');
                    Botble.handleError(res);
                }
            });
        });

        $(document).on('click', '.btn-begin-translate-all', function(event) {
            event.preventDefault();
            event.stopPropagation();
            $(this).prop('disabled', true).addClass('button-loading');

            $.ajax({
                type: 'POST',
                cache: false,
                url: $(this).closest('form').prop('action'),
                data: new FormData($(this).closest('form')[0]),
                contentType: false,
                processData: false,
                success: res => {
                    if (!res.error) {
                        Botble.showSuccess(res.message);
                    } else {
                        Botble.showError(res.message);
                    }
                    location.reload();
                    $(this).prop('disabled', false).removeClass('button-loading');
                },
                error: res => {
                    $(this).prop('disabled', false).removeClass('button-loading');
                    Botble.handleError(res);
                }
            });
        });
    </script>
@endpush
