<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Transaction;
use App\Traits\HasApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    use HasApiResponses;

    public function index(Request $request)
    {
        try {
            $transction = Transaction::orderBy('created_at', 'asc')->with('item.type')->paginate($request->input('limit', 10));
            $data = [
                'data' => $transction->items(),
                'total' => $transction->total(),
                'current_page' => $transction->currentPage(),
                'last_page' => $transction->lastPage(),
            ];
            return $this->success($data, 'Transaction fetched successfully', 200);
        } catch (\Throwable $th) {
            return $this->error($th, 422);
        }
    }

    public function show($id)
    {
        try {
            $transction = Transaction::with('item.type')->find($id);
            return $this->success($transction, 'Transaction fetched successfully', 200);
        } catch (\Throwable $th) {
            return $this->error($th, 422);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:items,id',
            'quantity_sold' => 'required|numeric',
            'date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 422);
        }

        try {
            $transaction = new Transaction;
            $old_stock = Item::find($request->input('item_id'))->stock;
            $transaction->old_stock = $old_stock;
            $transaction->item_id = $request->input('item_id');
            $transaction->quantity_sold = $request->input('quantity_sold');
            $transaction->date = $request->input('date');
            $transaction->save();

            $stock = $old_stock - $request->input('quantity_sold');
            $item = Item::find($request->input('item_id'));
            $item->stock = $stock;
            $item->save();

            return $this->success($transaction, 'Transaction created successfully', 200);
        } catch (\Throwable $th) {
            return $this->error($validator->errors(), 422);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:items,id',
            'quantity_sold' => 'required|numeric',
            'date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 422);
        }

        try {
            $transaction = Transaction::find($id);
            $old_stock = Item::find($request->input('item_id'))->stock + $transaction->quantity_sold;
            $transaction->old_stock = $old_stock;
            $transaction->item_id = $request->input('item_id');
            $transaction->quantity_sold = $request->input('quantity_sold');
            $transaction->date = $request->input('date');
            $transaction->save();

            $stock = $old_stock - $request->input('quantity_sold');
            $item = Item::find($request->input('item_id'));
            $item->stock = $stock;
            $item->save();
            return $this->success($transaction, 'Transaction updated successfully', 200);
        } catch (\Throwable $th) {
            return $this->error($validator->errors(), 422);
        }
    }

    public function destroy($id)
    {
        try {
            $transaction = Transaction::find($id);
            $item = Item::find($transaction->item_id);
            $item->stock = $item->stock + $transaction->quantity_sold;
            $item->save();
            $transaction->delete();
            return $this->success(null, 'Transaction deleted successfully', 200);
        } catch (\Throwable $th) {
            return $this->error($th, 422);
        }
    }
}
