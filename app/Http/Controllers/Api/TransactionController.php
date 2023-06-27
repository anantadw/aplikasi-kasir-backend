<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $transactions = Transaction::all();

            return response()->json([
                'code' => 200,
                'data' => $transactions
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
                'admin' => 'required',
                'total_price' => 'required',
                'rounded_total_price' => 'required',
                'payment_method' => 'required',
                'items' => 'required|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 400,
                    'errors' => $validator->errors()
                ], 400);
            }

            $transaction = Transaction::create(Arr::except($validator->validated(), 'items'));

            $items = $validator->validated()['items'];
            foreach ($items as $item) {
                $transaction->menus()->attach($item['id'], [
                    'amount' => $item['amount'],
                    'price' => $item['price']
                ]);
            }

            return response()->json(['code' => 201], 201);
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
            $transaction = Transaction::with('detailTransactions')->findOrFail($id);

            return response()->json([
                'status' => 200,
                'data' => $transaction
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
