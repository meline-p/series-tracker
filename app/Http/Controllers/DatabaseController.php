<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;

class DatabaseController extends Controller
{
    public function export()
    {
        Artisan::call('db:export');

        return redirect()->route('series.index');
    }

    public function import()
    {
        Artisan::call('db:import');

        return redirect()->route('series.index');
    }
}
