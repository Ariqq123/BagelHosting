<?php

use Illuminate\Support\Facades\Route;
use Pterodactyl\BlueprintFramework\Extensions\freeservers\FreeServersController;

// Blueprint automatically prefixes with /api/client/extensions/freeservers/
// So these become:
// GET  /api/client/extensions/freeservers/
// POST /api/client/extensions/freeservers/create
// GET  /api/client/extensions/freeservers/my-servers

Route::get('/', [FreeServersController::class, 'index']);
Route::post('/create', [FreeServersController::class, 'create']);
Route::post('/extend', [FreeServersController::class, 'extend']);
Route::get('/my-servers', [FreeServersController::class, 'myServers']);
