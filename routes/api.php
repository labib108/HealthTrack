<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All API routes are versioned.
| Current version: v1
|
*/

Route::prefix('v1')
    ->group(base_path('routes/api/v1.php'));
