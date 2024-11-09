<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Type;
use App\Traits\HasApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TypeController extends Controller
{
    use HasApiResponses;

    public function index(Request $request)
    {
        $types = Type::orderBy('created_at', 'desc')->paginate($request->input('limit', 10));
        try {
            $data = [
                'data' => $types->items(),
                'total' => $types->total(),
                'current_page' => $types->currentPage(),
                'last_page' => $types->lastPage(),
            ];
            return $this->success($data, 'Type fetched successfully', 200);
        } catch (\Throwable $th) {
            return $this->error($th, 422);
        }
    }

    public function show($id)
    {
        try {
            $type = Type::find($id);
            return $this->success($type, 'Type fetched successfully', 200);
        } catch (\Throwable $th) {
            return $this->error($th, 422);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 422);
        }

        try {
            $type = new Type;
            $type->type = $request->input('type');
            $type->save();
            return $this->success($type, 'Type created successfully', 200);
        } catch (\Throwable $th) {
            return $this->error($validator->errors(), 422);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 422);
        }

        try {
            $type = Type::find($id);
            $type->type = $request->input('type');
            $type->save();
            return $this->success($type, 'Type Updated successfully', 200);
        } catch (\Throwable $th) {
            return $this->error($validator->errors(), 422);
        }
    }

    public function destroy($id)
    {
        try {
            $type = Type::find($id);
            $type->delete();
            return $this->success(null, 'Type deleted successfully', 200);
        } catch (\Throwable $th) {
            return $this->error($th, 422);
        }
    }
}
