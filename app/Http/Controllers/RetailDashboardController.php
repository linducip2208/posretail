<?php

namespace App\Http\Controllers;

use App\Services\RetailDashboardData;

class RetailDashboardController extends Controller
{
    public function index()
    {
        return view('app.dashboard', RetailDashboardData::get());
    }
}
