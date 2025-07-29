<?php

namespace VigStudio\VigSeo\Facades;

use Illuminate\Support\Facades\Facade;

class ContentAnalyze extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'vig-seo-analyzer';
    }
}
