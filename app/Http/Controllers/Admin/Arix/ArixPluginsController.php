<?php

namespace Pterodactyl\Http\Controllers\Admin\Arix;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Pterodactyl\Models\ArixPlugin;

class ArixPluginsController extends ArixBaseController
{
    public function index(): View
    {
        return view('admin.arix.plugins', [
            'plugins' => ArixPlugin::query()->orderBy('name')->get(),
        ]);
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
