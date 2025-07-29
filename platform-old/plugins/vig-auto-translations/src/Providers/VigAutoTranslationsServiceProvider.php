<?php

namespace VigStudio\VigAutoTranslations\Providers;

use Botble\Base\Facades\PanelSectionManager;
use Botble\Base\PanelSections\PanelSectionItem;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Setting\PanelSections\SettingOthersPanelSection;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\ServiceProvider;
use VigStudio\VigAutoTranslations\Commands\AutoTranslateCommand;
use VigStudio\VigAutoTranslations\Manager;
use VigStudio\VigAutoTranslations\Services\AWSTranslator;
use VigStudio\VigAutoTranslations\Services\ChatGPTTranslator;
use VigStudio\VigAutoTranslations\Services\GoogleTranslator;

class VigAutoTranslationsServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->setNamespace('plugins/vig-auto-translations')->loadHelpers();

        $this->app->singleton(Manager::class, function () {
            $manager = new Manager();

            $driver = setting('vig_translate_driver');
            $withoutDatabase = setting('vig_translate_without_database', false);

            return match ($driver) {
                'chatgpt' => $manager->setDriver(new ChatGPTTranslator())->setWithoutDatabase($withoutDatabase),
                'aws' => $manager->setDriver(new AWSTranslator())->setWithoutDatabase($withoutDatabase),
                default => $manager->setDriver(new GoogleTranslator())->setWithoutDatabase($withoutDatabase),
            };
        });
    }

    public function boot(): void
    {
        $this
            ->loadAndPublishConfigurations(['permissions', 'general'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes();

        $this->commands(AutoTranslateCommand::class);

        $this->app->booted(function () {
            $this->app->register(HookServiceProvider::class);
        });

        PanelSectionManager::default()->beforeRendering(function () {
            PanelSectionManager::registerItem(
                SettingOthersPanelSection::class,
                fn () => PanelSectionItem::make('blog')
                    ->setTitle('VigAutoTranslations')
                    ->withIcon('ti ti-language')
                    ->withDescription('Dịch tự động')
                    ->withPriority(120)
                    ->withRoute('vig-auto-translations.settings')
            );
        });

        if (version_compare('7.0.0', get_core_version(), '>=')) {
            $this->app['events']->listen(RouteMatched::class, function () {
                dashboard_menu()
                    ->registerItem([
                        'id' => 'cms-plugins-vig-auto-translations',
                        'priority' => 80,
                        'parent_id' => 'cms-plugin-translation',
                        'name' => 'plugins/vig-auto-translations::vig-auto-translations.name_theme',
                        'icon' => null,
                        'url' => route('vig-auto-translations.theme'),
                        'permissions' => ['vig-auto-translations.index'],
                    ]);
            });
        }
    }
}
