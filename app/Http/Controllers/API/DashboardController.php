<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Type;
use App\Traits\HasApiResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use HasApiResponses;

    public function index(Request $request)
    {
        $queryDaterange = "";
        if ($request->has("daterange") && $request->input("daterange") != null) {
            $daterange = explode(' - ', $request->input('daterange'));
            if (count($daterange) === 2) {
                $start_date = Carbon::parse($daterange[0])->format('Y-m-d');
                $end_date = Carbon::parse($daterange[1])->format('Y-m-d');
                $queryDaterange = "WHERE transactions.date BETWEEN '$start_date' AND '$end_date'";
            } else {
                $daterange = $request->input('daterange');
                $queryDaterange = "WHERE transactions.date = '$daterange'";
            }
        }

        $sort = 'desc';
        if ($request->input('sort')) {
            $sort = $request->input('sort');
        }
        $types = DB::select("
            SELECT types.id, 
                    types.type, 
                    COUNT(items.id) AS items_count,
                    SUM(items.stock) AS items_stock_sum,
                    COALESCE(SUM(transactions.quantity_sold), 0) AS quantity_sold
                FROM types
                LEFT JOIN items ON items.type_id = types.id
                LEFT JOIN transactions ON transactions.item_id = items.id
                $queryDaterange
                GROUP BY types.id, types.type
                ORDER BY quantity_sold $sort;
        ");

        return $this->success($types, 'Type fetched successfully', 200);
    }
}
