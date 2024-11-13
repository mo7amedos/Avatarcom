<?php

namespace VigStudio\VigAutoTranslations\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Language;
use Botble\Translation\Http\Requests\TranslationRequest;
use Botble\Translation\Manager;
use Botble\Translation\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use VigStudio\VigAutoTranslations\Manager as AutoTranslateManager;

class VigAutoTranslationsController extends BaseController
{
    public function __construct(protected AutoTranslateManager $autoTranslateManager)
    {
    }

    public function getThemeTranslations(Request $request)
    {
        page_title()->setTitle(trans('plugins/vig-auto-translations::vig-auto-translations.title'));

        Assets::addScripts(['bootstrap-editable'])
            ->addStyles(['bootstrap-editable'])
            ->addScriptsDirectly('vendor/core/plugins/translation/js/theme-translations.js')
            ->addStylesDirectly('vendor/core/plugins/translation/css/theme-translations.css');

        $data = $this->getDataTranslations($request->input('ref_lang'));

        $translations = $data['translations'];
        $groups = $data['groups'];
        $group = $data['group'];
        $defaultLanguage = $data['defaultLanguage'];

        return view(
            'plugins/vig-auto-translations::theme-translations',
            compact('translations', 'groups', 'group', 'defaultLanguage')
        );
    }

    public function getDataTranslations(string|null $refLang): array
    {
        $groups = Language::getAvailableLocales();
        $defaultLanguage = Arr::get($groups, 'en');

        if (! $refLang) {
            $group = $defaultLanguage;
        } else {
            $group = Arr::first(
                Arr::where($groups, function ($item) use ($refLang) {
                    return $item['locale'] == $refLang;
                })
            );
        }

        $translations = $this->autoTranslateManager->getThemeTranslations($group['locale']);

        return [
            'translations' => $translations,
            'groups' => $groups,
            'group' => $group,
            'defaultLanguage' => $defaultLanguage,
        ];
    }

    public function postThemeTranslations(Request $request, BaseHttpResponse $response)
    {
        if (! File::isDirectory(lang_path())) {
            File::makeDirectory(lang_path());
        }

        if (! File::isWritable(lang_path())) {
            return $response
                ->setError()
                ->setMessage(
                    trans('plugins/translation::translation.folder_is_not_writeable', ['lang_path' => lang_path()])
                );
        }

        $locale = $request->input('pk');
        $name = $request->input('name');
        $value = $request->input('value');

        if ($request->input('auto') == 'true') {
            $value = $this->autoTranslateManager->translate('en', $locale, $name);
        }

        if ($locale) {
            $translations = $this->autoTranslateManager->getThemeTranslations($locale);

            if ($request->has('name') && Arr::has($translations, $request->input('name'))) {
                $translations[$request->input('name')] = $value;
            }

            $this->autoTranslateManager->saveThemeTranslations($locale, $translations);
        }

        return $response
            ->setPreviousUrl(route('vig-auto-translations.theme'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function postThemeAllTranslations(Request $request, BaseHttpResponse $response)
    {
        $locale = $request->input('locale');
        $data = $this->getDataTranslations($locale);
        $translations = $data['translations'];

        BaseHelper::maximumExecutionTimeAndMemoryLimit();

        foreach ($translations as $key => $translation) {
            if ($key !== $translation) {
                continue;
            }

            $translations[$key] = $this->autoTranslateManager->translate('en', $locale, $key);
        }

        $this->autoTranslateManager->saveThemeTranslations($locale, $translations);

        return $response
            ->setPreviousUrl(route('vig-auto-translations.theme'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function getPluginsTranslations(Request $request)
    {
        page_title()->setTitle(trans('plugins/translation::translation.translations'));

        Assets::addScripts(['bootstrap-editable'])
            ->addStyles(['bootstrap-editable'])
            ->addStylesDirectly('vendor/core/plugins/translation/css/translation.css');

        $group = $request->input('group');
        $refLang = $request->input('ref_lang');

        $translations = $this->getLang();

        $locales = Language::getAvailableLocales();

        $allTranslations = Translation::query()
            ->where('group', $group)
            ->where('locale', $refLang)
            ->orderBy('key')
            ->get();
        $translationData = [];
        foreach ($allTranslations as $translation) {
            $translationData[$translation->key] = $translation;
        }

        return view('plugins/vig-auto-translations::plugin-translations')
            ->with('translations', $translations)
            ->with('translationData', $translationData)
            ->with('locales', $locales)
            ->with('group', $group)
            ->with('ref_lang', $refLang)
            ->with('editUrl', route('translations.group.edit', ['group' => $group]));
    }

    public function getLang(): array
    {
        $basePath = base_path();
        $arrayPathGet = [
            'core' => $basePath . '/platform/core',
            'packages' => $basePath . '/platform/packages',
            'plugins' => $basePath . '/platform/plugins',
        ];

        $langArray = [];

        foreach ($arrayPathGet as $key => $vendorPath) {
            if (is_dir($vendorPath)) {
                $packages = File::directories($vendorPath);
                foreach ($packages as $package) {
                    $packageName = basename($package);
                    $packagePath = $vendorPath . '/' . $packageName;

                    $langPath = $packagePath . '/resources/lang';
                    if (! is_dir($langPath)) {
                        continue;
                    }

                    $files = File::allFiles($langPath);
                    foreach ($files as $file) {
                        $info = pathinfo($file);
                        $group = $info['filename'];
                        $subLangPath = str_replace($langPath . DIRECTORY_SEPARATOR, '', $info['dirname']);
                        $subLangPath = str_replace(DIRECTORY_SEPARATOR, '/', $subLangPath);

                        if ($subLangPath != $langPath) {
                            $group = substr($subLangPath, 0, -3) . '/' . $group;
                        }
                        $filePath = $key . '/' . $packageName . $group;

                        if (! is_readable($file->getPathname())) {
                            continue;
                        }

                        $translations = require $file->getPathname();
                        $langArray[$filePath] = Arr::dot($translations);
                    }
                }
            }
        }

        return $langArray;
    }

    public function postAllPluginsTranslations(Request $request, BaseHttpResponse $response)
    {
        $group = $request->input('group');
        $locale = $request->input('ref_lang');

        $allTranslations = $this->getLang()[$group];

        foreach ($allTranslations as $key => $value) {
            $value = $this->autoTranslateManager->translate('en', $locale, $value);
            $this->firstOrNewTranslation($locale, $group, $key, $value);
        }

        return $response;
    }

    public function postPluginsTranslations(TranslationRequest $request, Manager $manager, BaseHttpResponse $response)
    {
        $group = $request->input('group');

        if (! in_array($group, $manager->getConfig('exclude_groups'))) {
            $name = $request->input('name');
            $value = $request->input('value');

            [$locale, $key] = explode('|', $name, 2);

            if ($request->input('auto') == 'true') {
                $value = $this->autoTranslateManager->translate('en', $locale, $value);
            }

            $this->firstOrNewTranslation($locale, $group, $key, $value);
        }

        return $response;
    }

    public function firstOrNewTranslation(string $locale, string $group, string $key, string $value): void
    {
        $translation = Translation::query()->firstOrNew([
            'locale' => $locale,
            'group' => $group,
            'key' => $key,
        ]);
        $translation->value = $value ?: null;
        $translation->status = Translation::STATUS_CHANGED;
        $translation->save();
    }

    public function getAutoTranslate(Request $request, BaseHttpResponse $response)
    {
        if (($locale = $request->input('locale')) &&
            ($name = $request->input('name')) &&
            in_array($locale, array_keys(Language::getAvailableLocales()))) {
            $value = $this->autoTranslateManager->translate('en', $locale, $name);

            return $response->setData([$locale => $value]);
        }

        return $response->setCode(404)->setError();
    }
}
