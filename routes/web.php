<?php

use Illuminate\Support\Facades\Route;

/**
 * Catch-all route for the Vue.js frontend.
 *
 * Any request that is not prefixed with "api" will return the "welcome" view,
 * which contains the Vue single-page application (SPA) entry point.
 *
 * The regular expression ensures that API routes (starting with /api)
 * are excluded and handled by routes/api.php instead.
 */
Route::view('/{any}', 'welcome')->where('any', '^(?!api).*$');