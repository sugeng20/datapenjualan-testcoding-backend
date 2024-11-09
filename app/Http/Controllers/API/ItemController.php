<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Traits\HasApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    use HasApiResponses;

    public function index(Request $request)
    {
        try {
            $item = Item::with('type')->paginate($request->input('limit', 10));
            $data = [
                'data' => $item->items(),
                'total' => $item->total(),
                'current_page' => $item->currentPage(),
                'last_page' => $item->lastPage(),
            ];
            return $this->success($data, 'Item fetched successfully', 200);
        } catch (\Throwable $th) {
            return $this->error($th, 422);
        }
    }

    public function show($id)
    {
        try {
            $item = Item::with('type')->find($id);
            return $this->success($item, 'Transaction fetched successfully', 200);
        } catch (\Throwable $th) {
            return $this->error($th, 422);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'stock' => 'required|numeric',
            'type_id' => 'required|exists:types,id',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 422);
        }

        try {
            $item = new Item;
            $item->name = $request->input('name');
            $item->stock = $request->input('stock');
            $item->type_id = $request->input('type_id');
            $item->save();
            return $this->success($item, 'Item created successfully', 200);
        } catch (\Throwable $th) {
            return $this->error($validator->errors(), 422);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'stock' => 'required|numeric',
            'type_id' => 'required|exists:types,id',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 422);
        }

        try {
            $item = Item::find($id);
            $item->name = $request->input('name');
            $item->stock = $request->input('stock');
            $item->type_id = $request->input('type_id');
            $item->save();
            return $this->success($item, 'Item created successfully', 200);
        } catch (\Throwable $th) {
            return $this->error($validator->errors(), 422);
        }
    }

    public function destroy($id)
    {
        try {
            $item = Item::find($id);
            $item->delete();
            return $this->success(null, 'Item deleted successfully', 200);
        } catch (\Throwable $th) {
            return $this->error($th, 422);
        }
    }
}
