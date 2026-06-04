<?php

namespace Pterodactyl\Http\Controllers\Admin\Arix;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;
use Pterodactyl\Models\ArixPlugin;

class ArixPluginsController extends ArixBaseController
{
    private const CURSEFORGE_API_KEY = 'settings::arix:plugins:curseforge_api_key';

    public function __construct(private SettingsRepositoryInterface $settings)
    {
    }

    public function index(): View
    {
        return view('admin.arix.plugins', [
            'plugins' => ArixPlugin::query()->orderBy('name')->get(),
            'curseforgeConfigured' => $this->settings->get(self::CURSEFORGE_API_KEY, '') !== '',
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'curseforge_api_key' => ['nullable', 'string', 'max:255'],
            'clear_curseforge_api_key' => ['nullable', 'boolean'],
        ]);

        if ((bool) ($data['clear_curseforge_api_key'] ?? false)) {
            $this->settings->forget(self::CURSEFORGE_API_KEY);

            return redirect()->route('admin.arix.plugins')->with('success', 'CurseForge API key removed.');
        }

        $key = trim((string) ($data['curseforge_api_key'] ?? ''));
        if ($key !== '') {
            $this->settings->set(self::CURSEFORGE_API_KEY, Crypt::encryptString($key));

            return redirect()->route('admin.arix.plugins')->with('success', 'CurseForge API key saved.');
        }

        return redirect()->route('admin.arix.plugins')->with('success', 'Marketplace settings unchanged.');
    }

    public function store(Request $request): RedirectResponse
    {
        ArixPlugin::query()->create($this->validated($request));

        return redirect()->route('admin.arix.plugins')->with('success', 'Plugin added.');
    }

    public function update(Request $request, ArixPlugin $plugin): RedirectResponse
    {
        $plugin->update($this->validated($request));

        return redirect()->route('admin.arix.plugins')->with('success', 'Plugin updated.');
    }

    public function delete(ArixPlugin $plugin): RedirectResponse
    {
        $plugin->delete();

        return redirect()->route('admin.arix.plugins')->with('success', 'Plugin deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'download_url' => ['required', 'string', 'url', 'starts_with:http://,https://'],
            'filename' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9._ -]+$/'],
            'icon_url' => ['nullable', 'string', 'url', 'starts_with:http://,https://'],
            'enabled' => ['nullable', 'boolean'],
        ]) + ['enabled' => false];
    }
}
