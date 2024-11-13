<?php

namespace VigStudio\VigSeo;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{
    public static function remove()
    {
        Schema::dropIfExists('vig_seos');
        Schema::dropIfExists('vig_seos_translations');
    }
}
