<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

/*
|--------------------------------------------------------------------------
| main route file
|--------------------------------------------------------------------------
|
*/

Route::get('/set-locale/{locale}', function ($locale) {
    if (in_array($locale, config('app.available_locales'))) {
        session(['locale' => $locale]);
        Session::put('direction', $locale === 'ar' ? 'rtl' : 'ltr');
        app()->setLocale($locale); // Immediately apply the locale for this request
    }
    return redirect()->back();
})->name('set-locale');

$basePath = base_path("routes");

if (File::exists("{$basePath}/dashboard.php")) {
    require "{$basePath}/dashboard.php";
}
if (File::exists("{$basePath}/website.php")) {
    require "{$basePath}/website.php";
}
