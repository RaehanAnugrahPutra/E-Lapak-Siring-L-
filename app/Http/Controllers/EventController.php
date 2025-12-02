<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;

class EventController extends Controller
{
    //index
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'List Event',
            'data' => Event::all()
        ], 200);
    }

    //show
    public function show($id)
    {
        $event = Event::find($id);
        if ($event) {
            return response()->json([
                'success' => true,
                'message' => 'Detail Event',
                'data' => $event
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Event tidak ditemukan',
            ], 404);
        }
    }

    //store validasi
    public function store(Request $request)
    {
        $request->validate([
            'nama_event' => 'required|string|max:255',
            'tanggal_event' => 'required|date',
            'lokasi_event' => 'required|string|max:255',
            // Add other validation rules as needed
        ]);
        $event = Event::create($request->all());
        return response()->json([
            'success' => true,
            'message' => 'Event berhasil ditambahkan',
            'data' => $event
        ], 201);
    }

    //update validasi
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_event' => 'required|string|max:255',
            'tanggal_event' => 'required|date',
            'lokasi_event' => 'required|string|max:255',
            // Add other validation rules as needed
        ]);
        $event = Event::find($id);
        if ($event) {
            $event->update($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Event berhasil diupdate',
                'data' => $event
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Event tidak ditemukan',
            ], 404);
        }
    }

    //destroy
    public function destroy($id)
    {
        $event = Event::find($id);
        if ($event) {
            $event->delete();
            return response()->json([
                'success' => true,
                'message' => 'Event berhasil dihapus',
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Event tidak ditemukan',
            ], 404);
        }
    }
}
