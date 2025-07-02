<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserApiController extends Controller
{
    public function index()
    {
        return response()->json(User::all());
    }

    public function informasi()
    {
        return response()->json(['message' => 'Ini halaman informasi umum']);
    }
}
