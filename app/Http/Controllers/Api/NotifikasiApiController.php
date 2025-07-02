<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotifikasiApiController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'Daftar notifikasi']);
    }

    public function kirim(Request $request)
    {
        return response()->json(['message' => 'Notifikasi berhasil dikirim']);
    }
}
