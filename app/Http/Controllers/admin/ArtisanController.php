<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
