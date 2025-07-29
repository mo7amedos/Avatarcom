<button class="btn btn-primary btn-get-auto-translate" type="button"
    data-bs-toggle="tooltip" title="{{ trans('plugins/vig-auto-translations::vig-auto-translations.translate') }}">
    <i class="fa-sharp fa-solid fa-language"></i>
</button>

<button class="btn btn-warning btn-reset-auto-translate @if ($item['key'] == $item['value']) d-none @endif"
    data-bs-toggle="tooltip" type="button" title="{{ trans('plugins/vig-auto-translations::vig-auto-translations.reset') }}">
    <i class="fa-solid fa-rotate-left"></i>
</button>
