<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class DashboardApiController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'Data dashboard berhasil diambil']);
    }
}
