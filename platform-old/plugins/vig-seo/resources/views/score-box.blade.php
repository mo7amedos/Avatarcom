<main class="container">
    <div class="form-group mb-3">
        <label for="seo_title" class="control-label">{{ trans('plugins/vig-seo::vig-seo.focus_keyphrase') }}</label>
        {!! Form::text('vig_seo_keywords', $meta ?? old('vig_seo_keywords'), ['class' => 'form-control', 'id' => 'seo_title', 'placeholder' => 'Focus keyphrase', 'data-counter' => 300]) !!}
        {{ Form::helper(trans('plugins/vig-seo::vig-seo.focus_keyphrase_helper')) }}
    </div>

    <div class="p-3 my-3 shadow-sm bg-blue-chambray-opacity">
        <div class="lh-1">
            <h1 class="h6 text-white mb-0 lh-1">{{ trans('plugins/vig-seo::vig-seo.keyword_density') }}</h1>
            <small class="text-white">{{ trans('plugins/vig-seo::vig-seo.keyword_density_helper') }}</small>
        </div>

        <ul class="list-group mt-2">
            @foreach ($data['getKeywords'] as $key => $keyword)
                @if (!empty($key))
                    <li class="list-group-item d-flex justify-content-between lh-sm">
                        <div>
                            <h6 class="my-0">{{ $key }}</h6>
                        </div>
                        <span class="text-muted">{{ trans('plugins/vig-seo::vig-seo.number_of_occurrences') }}: {{ count($keyword) }}</span>
                    </li>
                @endif
            @endforeach
        </ul>

    </div>

    <div class="p-3 my-3 shadow-sm bg-blue-chambray-opacity">
        <div class="lh-1">
            <h1 class="h6 text-white mb-0 lh-1">{{ trans('plugins/vig-seo::vig-seo.keywords_suggest') }}</h1>
            <small class="text-white">{{ trans('plugins/vig-seo::vig-seo.keywords_suggest_helper') }}</small>
        </div>


        @foreach ($data['keywords'] as $key => $keywords)
            <span class="badge badge-pill badge-info">{{ $key }} ({{ count($keywords) }})</span>
        @endforeach


    </div>


    <div class="d-flex align-items-center p-3 my-3 text-white bg-purple shadow-sm">
        <div class="lh-1">
            <h1 class="h6 mb-0 text-white lh-1">{{ trans('plugins/vig-seo::vig-seo.code_to_text_ratio') }}: {{ number_format($data['mainText']['code_to_text_ratio']) }}%</h1>
            <small>{{ trans('plugins/vig-seo::vig-seo.code_to_text_ratio_helper') }}</small>
        </div>
    </div>

    <div class="d-flex align-items-center p-3 my-3 text-white bg-purple shadow-sm">
        <div class="lh-1">
            <h1 class="h6 text-white mb-0 lh-1">{{ trans('plugins/vig-seo::vig-seo.word_count') }}: {{ number_format($data['mainText']['word_count']) }}</h1>
            <small>{{ trans('plugins/vig-seo::vig-seo.word_count_helper') }}</small>
        </div>
    </div>

    <div class="p-3 my-3 shadow-sm bg-blue-chambray-opacity">
        <div class="lh-1">
            <h1 class="h6 text-white mb-0 lh-1">{{ trans('plugins/vig-seo::vig-seo.headers') }}</h1>
            <small class="text-white">{{ trans('plugins/vig-seo::vig-seo.headers_helper') }}</small>
        </div>

        <ul class="list-group mt-2">
            @foreach ($data['headers'] as $key => $header)
                <li class="list-group-item d-flex justify-content-between lh-sm">
                    <div>
                        <h6 class="my-0">{{ $key }}</h6>
                        @foreach ($header as $value)
                            <small class="text-muted">- {!! $value['text'] !!}</small><br />
                        @endforeach
                    </div>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="p-3 my-3 shadow-sm bg-blue-chambray-opacity">
        <div class="lh-1">
            <h1 class="h6 text-white mb-0 lh-1">{{ trans('plugins/vig-seo::vig-seo.links') }}</h1>
            <small class="text-white">{{ trans('plugins/vig-seo::vig-seo.links_helper') }}</small>
        </div>

        <ul class="list-group mt-2">
            @foreach ($data['links'] as $key => $links)
                <li class="list-group-item d-flex justify-content-between lh-sm">
                    <div>
                        <h6 class="my-0">{{ $key }} ({{ count($links) }})</h6>
                        @foreach ($links as $value)
                            <small class="text-muted">- {{ $value }}</small><br />
                        @endforeach
                    </div>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="p-3 my-3 shadow-sm bg-blue-chambray-opacity">
        <div class="lh-1">
            <h1 class="h6 text-white mb-0 lh-1">{{ trans('plugins/vig-seo::vig-seo.images') }}</h1>
            <small class="text-white">{{ trans('plugins/vig-seo::vig-seo.images_helper') }}</small>
        </div>

        <ul class="list-group mt-2">
            @foreach ($data['images'] as $key => $images)
                <li class="list-group-item d-flex justify-content-between lh-sm">
                    <div>
                        <h6 class="my-0">{{ $key }} ({{ count($images) }})</h6>
                        @foreach ($images as $value)
                            <small class="text-muted">- src: {{ $value['src'] }} {{ !empty($value['alt']) ? '- alt: ' . $value['alt'] : '' }}</small><br />
                        @endforeach
                    </div>
                </li>
            @endforeach
        </ul>
    </div>

</main>
