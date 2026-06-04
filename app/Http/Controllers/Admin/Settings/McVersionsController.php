<?php

namespace Pterodactyl\Http\Controllers\Admin\Settings;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Minecraft\McVersionsEggGeneratorService;

class McVersionsController extends Controller
{
    public function __construct(
        private readonly AlertsMessageBag $alert,
        private readonly McVersionsEggGeneratorService $generator,
    ) {
    }

    public function index(): View
    {
        return view('admin.settings.mc-versions', [
            'preview' => $this->generator->preview(),
            'syncResult' => session('mc_versions_sync'),
        ]);
    }

    public function sync(): RedirectResponse
    {
        $result = $this->generator->sync();

        $this->alert->success(sprintf(
            'MC Versions sync complete: %d created, %d updated, %d skipped.',
            $result['created'],
            $result['updated'],
            $result['skipped'],
        ))->flash();

        foreach ($result['failed'] as $failure) {
            $this->alert->warning(sprintf('%s skipped: %s', $failure['name'], $failure['reason']))->flash();
        }

        return redirect()->route('admin.settings.mc-versions')->with('mc_versions_sync', $result);
    }
}
