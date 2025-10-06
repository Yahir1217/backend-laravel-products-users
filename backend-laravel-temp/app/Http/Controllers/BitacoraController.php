<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use Illuminate\Http\Request;
use MongoDB\BSON\ObjectId;

class BitacoraController extends Controller
{
    // Listar todos los registros de bitácora
    public function index()
    {
        return response()->json(Bitacora::all());
    }
}
