<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stand;
use Illuminate\Support\Facades\Validator;

class StandController extends Controller
{
    //crud stand api json
    public function index()
    {
       return response()->json([
            'success' => true,
            'message' => 'List Stand',
            'data' => Stand::all()
        ], 200);
    }

    public function show($id)
    {
        $stand = Stand::find($id);
        if ($stand) {
            return response()->json([
                'success' => true,
                'message' => 'Detail Stand',
                'data' => $stand
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Stand tidak ditemukan',
            ], 404);
        }
    }


    public function store(Request $request)
    {
        $request->validate([
            'kode_stand' => 'required|string|size:3|unique:stands,kode_stand',
            'harga_sewa' => 'nullable|integer|min:0',
            'status_stand' => 'nullable|in:kosong,terisi,maintenance',
        ]);
        $stand = Stand::create($request->all());
        return response()->json([
            'success' => true,
            'message' => 'Stand berhasil ditambahkan',
            'data' => $stand
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_stand' => 'required|string|size:3|unique:stands,kode_stand,'.$id,
            'harga_sewa' => 'nullable|integer|min:0',
            'status_stand' => 'nullable|in:kosong,terisi,maintenance',
        ]);
        $stand = Stand::find($id);
        if ($stand) {
            $stand->update($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Stand berhasil diupdate',
                'data' => $stand
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Stand tidak ditemukan',
            ], 404);
        }
    }

    public function destroy($id)
    {
        $stand = Stand::find($id);
        if ($stand) {
            $stand->delete();
            return response()->json([
                'success' => true,
                'message' => 'Stand berhasil dihapus',
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Stand tidak ditemukan',
            ], 404);
        }
    }

}
