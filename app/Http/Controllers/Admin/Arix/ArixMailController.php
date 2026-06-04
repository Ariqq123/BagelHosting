<?php

namespace Pterodactyl\Http\Controllers\Admin\Arix;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArixMailController extends ArixBaseController
{
    public function index(): View
    {
        return $this->arixView('admin.arix.mail');
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->saveArixConfig($request);
    }
}

