<?php

namespace App\Http\Controllers\admin;

use App\Enums\ArtisanStatus;
use App\Http\Controllers\Controller;
use App\Models\Artisan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArtisanController extends Controller
{
    /**
     * Display a listing of all artisans and filterable statuses.
     */
    public function index(): View
    {
        return view('admin.artisans.index');
    }
}
