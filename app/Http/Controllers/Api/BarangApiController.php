<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BarangDetailResource;
use App\Http\Resources\BarangResourceCollection;
use App\Models\Barang;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BarangApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $barang = Barang::all();
            return response()->json([
                'status' => 200,
                'data' => $barang
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'barcode' => 'required',
                'nama' => 'required',
                'harga' => 'required',
                'stok' => 'required',
                'jenis' => 'required',
                'gambar' => 'required|image',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 400,
                    'errors' => $validator->errors()
                ], 400);
            }

            $data = $validator->validated();
            $gambar = $validator->validated()['gambar'];
            $nama_gambar = $gambar->hashName();
            $gambar->storeAs('public/images', $nama_gambar);
            $data['gambar'] = $nama_gambar;

            $barang = Barang::create($data);

            return response()->json([
                'status' => 201,
                'data' => $barang
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $barang = Barang::findOrFail($id);
            return response()->json([
                'status' => 200,
                'data' => $barang
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 404,
                'message' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $barang = Barang::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'barcode' => 'required',
                'nama' => 'required',
                'harga' => 'required',
                'stok' => 'required',
                'jenis' => 'required',
                'gambar' => 'nullable|image',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 400,
                    'errors' => $validator->errors()
                ], 400);
            }

            $data = $validator->validated();
            if ($data['gambar']) {
                Storage::delete('public/images/' . $barang->gambar);
                $gambar = $validator->validated()['gambar'];
                $nama_gambar = $gambar->hashName();
                $gambar->storeAs('public/images', $nama_gambar);
                $data['gambar'] = $nama_gambar;
            }
            $barang->update($data);

            return response()->json([
                'status' => 200,
                'data' => $barang
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 404,
                'message' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $barang = Barang::findOrFail($id);
            Storage::delete('public/images/' . $barang->gambar);
            $barang->delete();
            return response()->json([
                'status' => 200
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 404,
                'message' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
