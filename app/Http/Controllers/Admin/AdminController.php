<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function users()
    {
        return Inertia::render('Admin/Users', [
            'users' => User::paginate(10),
        ]);
    }

    public function settings()
    {
        return Inertia::render('Admin/Settings', [
            'settings' => [
                'site_name' => config('app.name'),
                'maintenance_mode' => app()->isDownForMaintenance(),
            ]
        ]);
    }
}
