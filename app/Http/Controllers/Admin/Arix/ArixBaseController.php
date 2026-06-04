<?php

namespace Pterodactyl\Http\Controllers\Admin\Arix;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Pterodactyl\Http\Controllers\Controller;

abstract class ArixBaseController extends Controller
{
    protected function arixView(string $view): View
    {
        $config = array_map(
            fn ($value) => is_bool($value) ? ($value ? 'true' : 'false') : $value,
            config('arix', [])
        );

        return view($view, $config);
    }

    protected function saveArixConfig(Request $request): RedirectResponse
    {
        $config = config('arix', []);

        foreach ($request->except('_token') as $key => $value) {
            if (!str_starts_with($key, 'arix:')) {
                continue;
            }

            $name = substr($key, 5);
            $config[$name] = $this->normalizeValue($value);
        }

        $contents = "<?php\n\nreturn " . var_export($config, true) . ";\n";

        File::put(config_path('arix.php'), $contents);
        Artisan::call('config:clear');

        return redirect()->back()->with('success', 'Arix configuration saved.');
    }

    private function normalizeValue(mixed $value): mixed
    {
        if ($value === 'true') {
            return true;
        }

        if ($value === 'false') {
            return false;
        }

        if (is_string($value) && preg_match('/^-?\d+$/', $value) === 1) {
            return (int) $value;
        }

        return $value;
    }
}
