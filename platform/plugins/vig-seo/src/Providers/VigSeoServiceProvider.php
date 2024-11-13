<?php

namespace VigStudio\VigSeo\Providers;

use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use VigStudio\VigSeo\Models\VigSeo;
use VigStudio\VigSeo\Repositories\Caches\VigSeoCacheDecorator;
use VigStudio\VigSeo\Repositories\Eloquent\VigSeoRepository;
use VigStudio\VigSeo\Repositories\Interfaces\VigSeoInterface;
use VigStudio\VigSeo\Services\ContentAnalyze;

class VigSeoServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register()
    {
        $this->app->bind(VigSeoInterface::class, function () {
            return new VigSeoCacheDecorator(new VigSeoRepository(new VigSeo()));
        });

        $this->app->singleton('vig-seo-analyzer', ContentAnalyze::class);

        $this->setNamespace('plugins/vig-seo')->loadHelpers();
    }

    public function boot()
    {
        $this
            ->loadAndPublishConfigurations(['permissions'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes(['web']);

        $this->app->booted(function () {
            $this->app->register(HookServiceProvider::class);
        });

        Event::listen(RouteMatched::class, function () {
            // dashboard_menu()->registerItem([
            //     'id' => 'cms-plugins-vig-seo',
            //     'priority' => 5,
            //     'parent_id' => null,
            //     'name' => 'plugins/vig-seo::vig-seo.name',
            //     'icon' => 'fa fa-list',
            //     'url' => route('vig-seo.index'),
            //     'permissions' => ['vig-seo.index'],
            // ]);
        });
    }

    public function provides()
    {
        return ['vig-seo-analyzer'];
    }
}
