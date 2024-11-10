<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Transaction;
use App\Traits\HasApiResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\select;

class TransactionController extends Controller
{
    use HasApiResponses;

    public function index(Request $request)
    {
        $getTransaction = Transaction::query();

        if ($request->has('search') || $request->has('sort_item_name')) {
            $getTransaction->join('items', 'transactions.item_id', '=', 'items.id')
                ->join('types', 'items.type_id', '=', 'types.id')
                ->select('transactions.id', 'transactions.date', 'items.name', 'transactions.quantity_sold', 'transactions.old_stock', 'types.type', 'transactions.created_at');
        }

        if ($request->has('search') && $request->input('search') != null) {
            try {
                $tanggal = Carbon::parse($request->input('search'))->format('Y-m-d');
            } catch (\Throwable $th) {
                $tanggal = $request->input('search');
            }
            $getTransaction->where('items.name', 'like', '%' . $request->input('search') . '%')
                ->orWhere('transactions.date', 'like', '%' . $tanggal . '%')
                ->orWhere('types.type', 'like', '%' . $request->input('search') . '%')
                ->orWhere('transactions.quantity_sold', 'like', '%' . $request->input('search') . '%')
                ->orWhere('transactions.old_stock', 'like', '%' . $request->input('search') . '%');
        }

        if ($request->has('sort_item_name') && in_array($request->input('sort_item_name'), ['asc', 'desc'])) {
            $getTransaction->orderBy('items.name', $request->input('sort_item_name'));
        }

        if ($request->has('sort_date') && in_array($request->input('sort_date'), ['asc', 'desc'])) {
            $getTransaction->orderBy('date', $request->input('sort_date'));
        }

        $transction = $getTransaction->orderBy('transactions.created_at', 'asc')->paginate($request->input('limit', 10));
        $data = [
            'data' => $transction->items(),
            'total' => $transction->total(),
            'current_page' => $transction->currentPage(),
            'last_page' => $transction->lastPage(),
        ];
        return $this->success($data, 'Transaction fetched successfully', 200);

        try {
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
