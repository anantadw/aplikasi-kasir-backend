<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $menus = Menu::with('category:id,name')->get();

            return response()->json([
                'code' => 200,
                'data' => $menus
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'code' => 500,
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
                'name' => 'required',
                'category_id' => 'required',
                'price' => 'required',
                'status' => 'nullable',
                'image' => 'required|image',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 400,
                    'errors' => $validator->errors()
                ], 400);
            }

            $data = $validator->validated();
            $image = $data['image'];
            $data['image'] = $image->hashName();

            DB::transaction(function () use ($data, $image) {
                $image->storeAs('/images/menu', $image->hashName());
                Menu::create($data);
            });

            return response()->json(['code' => 201,], 201);
        } catch (Exception $e) {
            return response()->json([
                'code' => 500,
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
            $menu = Menu::with('category:id,name')->findOrFail($id);

            return response()->json([
                'code' => 200,
                'data' => $menu
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'code' => 404,
                'message' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'code' => 500,
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
            $menu = Menu::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    Rule::unique('menus', 'name')->ignore($id)
                ],
                'category_id' => 'required',
                'price' => 'required',
                'status' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 400,
                    'errors' => $validator->errors()
                ], 400);
            }

            $data = $validator->validated();
            DB::transaction(function () use ($request, $menu, $data) {
                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $image->storeAs('/images/menu/', $image->hashName());
                    Storage::delete('/images/menu/' . basename($menu->image));

                    $data['image'] = $image->hashName();
                }
                $menu->update($data);
            });

            return response()->json(['code' => 200], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'code' => 404,
                'message' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'code' => 500,
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
            $menu = Menu::findOrFail($id);
            Storage::delete('/images/menu/' . basename($menu->image));
            $menu->delete();

            return response()->json(['code' => 200], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'code' => 404,
                'message' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
