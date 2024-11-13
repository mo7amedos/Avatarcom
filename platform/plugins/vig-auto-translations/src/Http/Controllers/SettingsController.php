<?php

namespace VigStudio\VigAutoTranslations\Http\Controllers;

use Botble\Setting\Http\Controllers\SettingController;
use Illuminate\Http\Request;
use VigStudio\VigAutoTranslations\Forms\Settings\AutoTranslateSettingForm;
use VigStudio\VigAutoTranslations\Http\Requests\SettingRequest;

class SettingsController extends SettingController
{
    public function settings(Request $request)
    {
        $this->pageTitle('Setting Vig Auto Translate');

        return AutoTranslateSettingForm::create()->renderForm();
    }

    public function update(SettingRequest $request)
    {
        return $this->performUpdate($request->validated());
    }
}
