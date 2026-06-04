<?php

namespace Pterodactyl\Http\Controllers\Admin\Arix;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArixStylingController extends ArixBaseController
{
    public function index(): View
    {
        return $this->arixView('admin.arix.styling');
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->saveArixConfig($request);
    }
}

