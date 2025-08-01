<?php

namespace Botble\Setting\Http\Controllers;

use Botble\Base\Exceptions\LicenseInvalidException;
use Botble\Base\Exceptions\LicenseIsAlreadyActivatedException;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Core;
use Botble\Base\Supports\Language;
use Botble\Setting\Facades\Setting;
use Botble\Setting\Forms\GeneralSettingForm;
use Botble\Setting\Http\Requests\GeneralSettingRequest;
use Botble\Setting\Http\Requests\LicenseSettingRequest;
use Botble\Setting\Models\Setting as SettingModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;

class GeneralSettingController extends SettingController
{
    public function edit()
    {
        $this->pageTitle(trans('core/setting::setting.general_setting'));

        $form = GeneralSettingForm::create();

        return view('core/setting::general', compact('form'));
    }

    public function update(GeneralSettingRequest $request): BaseHttpResponse
    {
        $data = Arr::except($request->input(), [
            'locale',
        ]);

        $locale = $request->input('locale');
        if ($locale && array_key_exists($locale, Language::getAvailableLocales())) {
            session()->put('site-locale', $locale);
        }

        $isDemoModeEnabled = BaseHelper::hasDemoModeEnabled();

        if (! $isDemoModeEnabled) {
            $data['locale'] = $locale;
        }

        cache()->forget('core.base.boot_settings');

        return $this->performUpdate($data);
    }

    public function getVerifyLicense(Request $request, Core $core)
    {
        if ($request->expectsJson() && ! $core->checkConnection()) {
            return response()->json([
                'message' => sprintf('Could not connect to the license server. Please try again later. Your site IP: %s', $core->getServerIP()),
            ], 400);
        }

        $invalidMessage = 'Your license is invalid. Please activate your license!';

        if (! $this->isLicenseExists($core)) {
            $this
                ->httpResponse()
                ->setData([
                    'html' => view('core/base::system.license-invalid')->render(),
                ]);

            return $this
                ->httpResponse()
                ->setError()
                ->setMessage($invalidMessage);
        }

        try {
            if (! $core->verifyLicense(true)) {
                return $this
                    ->httpResponse()
                    ->setError()
                    ->setMessage($invalidMessage);
            }

            $activatedAt = $this->getLicenseActivatedDate($core);

            $data = [
                'activated_at' => $activatedAt->format('M d Y'),
                'licensed_to' => setting('licensed_to'),
            ];

            $core->clearLicenseReminder();

            return $this
                ->httpResponse()
                ->setMessage('Your license is activated.')->setData($data);
        } catch (Throwable $exception) {
            return $this
                ->httpResponse()
                ->setMessage($exception->getMessage());
        }
    }

    public function activateLicense(LicenseSettingRequest $request, Core $core): BaseHttpResponse
    {
        $buyer = $request->input('buyer');

        if (filter_var($buyer, FILTER_VALIDATE_URL)) {
            $username = Str::afterLast($buyer, '/');

            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(sprintf('Envato username must not a URL. Please try with username "%s".', $username));
        }

        $purchasedCode = $request->input('purchase_code');

        try {
            $core->activateLicense($purchasedCode, $buyer);

            $data = $this->saveActivatedLicense($core, $buyer);

            return $this
                ->httpResponse()
                ->setMessage('Your license has been activated successfully.')
                ->setData($data);
        } catch (LicenseInvalidException | LicenseIsAlreadyActivatedException $exception) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage($exception->getMessage());
        } catch (Throwable $exception) {
            report($exception);

            return $this
                ->httpResponse()
                ->setError()
                ->setMessage($exception->getMessage() ?: 'Something went wrong. Please try again later.');
        }
    }

    public function deactivateLicense(Core $core)
    {
        try {
            $core->deactivateLicense();

            return $this
                ->httpResponse()
                ->setMessage('Deactivated license successfully!');
        } catch (Throwable $exception) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function resetLicense(LicenseSettingRequest $request, Core $core)
    {
        try {
            if (! $core->revokeLicense($request->input('purchase_code'), $request->input('buyer'))) {
                return $this
                    ->httpResponse()
                    ->setError()
                    ->setMessage('Could not reset your license.');
            }

            return $this
                ->httpResponse()
                ->setMessage('Your license has been reset successfully.');
        } catch (Throwable $exception) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    protected function saveActivatedLicense(Core $core, string $buyer): array
    {
        Setting::forceSet('licensed_to', $buyer)->save();

        $activatedAt = $this->getLicenseActivatedDate($core);

        $core->clearLicenseReminder();

        return [
            'activated_at' => $activatedAt->format('M d Y'),
            'licensed_to' => $buyer,
        ];
    }

    private function getLicenseActivatedDate(Core $core): Carbon
    {
        if (config('core.base.general.license_storage_method') === 'database') {
            // For database storage, use the setting's updated_at timestamp or current time
            $licenseContent = SettingModel::query()->where('key', 'license_file_content')->first();

            return $licenseContent && $licenseContent->updated_at
                ? Carbon::parse($licenseContent->updated_at)
                : Carbon::now();
        }

        // For file storage, use file creation time
        return Carbon::createFromTimestamp(filectime($core->getLicenseFilePath()));
    }

    private function isLicenseExists(Core $core): bool
    {
        if (config('core.base.general.license_storage_method') === 'database') {
            return Setting::has('license_file_content') && ! empty(Setting::get('license_file_content'));
        }

        return File::exists($core->getLicenseFilePath());
    }
}
