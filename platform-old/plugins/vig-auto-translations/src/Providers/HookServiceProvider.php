<?php

namespace VigStudio\VigAutoTranslations\Providers;

use Assets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Yajra\DataTables\CollectionDataTable;
use Yajra\DataTables\EloquentDataTable;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter(BASE_FILTER_AFTER_SETTING_CONTENT, [$this, 'addSettings'], 99);

        if (is_plugin_active('translation')) {
            add_filter(BASE_FILTER_GET_LIST_DATA, [$this, 'addColumnToTranslationTable'], 153, 2);
            add_filter(BASE_FILTER_TABLE_HEADINGS, [$this, 'addHeadingToTranslationTable'], 153, 2);
        }
    }

    public function addSettings(?string $data = null): string
    {
        return $data . view('plugins/vig-auto-translations::setting')->render();
    }

    public function addHeadingToTranslationTable(array $headings, Model|string|null $model): array
    {
        if (
            ! $model ||
            ! is_in_admin(true) ||
            ! Route::is('translations.theme-translations') ||
            ! Auth::user()->hasPermission('vig-auto-translations.index')
        ) {
            return $headings;
        }

        Assets::addScriptsDirectly(['vendor/core/plugins/vig-auto-translations/js/auto-translations.js']);

        return array_merge($headings, [
            'action' => [
                'title' => trans('plugins/vig-auto-translations::vig-auto-translations.actions'),
                'class' => 'text-center',
                'width' => '120',
                'searchable' => false,
                'orderable' => false,
            ],
        ]);
    }

    public function addColumnToTranslationTable(EloquentDataTable|CollectionDataTable $data, Model|string|null $model): EloquentDataTable|CollectionDataTable
    {
        if (
            ! $model ||
            ! is_in_admin(true) ||
            ! Route::is('translations.theme-translations') ||
            ! Auth::user()->hasPermission('vig-auto-translations.index')
        ) {
            return $data;
        }

        return $data->editColumn('action', function ($item) {
            return view('plugins/vig-auto-translations::actions', compact('item'));
        });
    }
}
