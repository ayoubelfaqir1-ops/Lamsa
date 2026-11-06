<?php

use Illuminate\Support\Facades\Route;
use Modules\CMS\Http\Controllers\Admin\PageController as AdminPageController;

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::apiResource('pages', AdminPageController::class);
});
