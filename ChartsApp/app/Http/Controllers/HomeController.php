<?php

namespace App\Http\Controllers;

use App\App\Models\PsHeader;
use App\Models\Branche;
use App\Models\PsItemSpec1;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use TheSeer\Tokenizer\Exception;
class HomeController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }




    public function index(Request $request){
        try{
            $last_date = PsHeader::select('PsDate')->orderBy('PsDate', 'desc')->first()->PsDate;
            $start_last_date_at = Carbon::parse($last_date)->format('Y/m/d');
            $end_last_date_at = Carbon::parse($last_date)->format('Y/m/d');
            $start_at = date($request->start_at) ? date($request->start_at) : $start_last_date_at;
            $end_at = date($request->end_at) ? date($request->end_at) :  $end_last_date_at;
            $query = PsHeader::select([DB::raw('PsBranch'), DB::raw('PsBranchCode'),  DB::raw('SUM(PsSales) as PsSales'), DB::raw('SUM(PsExpenses) as PsExpenses')])->whereBetween('PsDate', [$start_at .  ' 00:00:00', $end_at .  ' 23:59:59'])->orderBy('PsSales', 'DESC')->groupBy('PsBranch', 'PsBranchCode')->get();
             if($query == ""){
                 return redirect()->back()->with('error', 'لا توجد بيانات فى هذا الوقت ');
             }
            $data['branches'][] = PsHeader::select(['PsBranch'])->whereBetween('PsDate', [$start_at, $end_at])->get();
            $data['sales'][] = PsHeader::select(['PsSales'])->whereBetween('PsDate', [$start_at, $end_at])->get();
            $data['expenses'][] = PsHeader::select(['PsExpenses'])->whereBetween('PsDate', [$start_at, $end_at])->get();
            $labels = [];
            $dataset = [];
            $sales = [];
            $expenses = [];
            foreach ($query as $q) {
                $labels['name'][] = $q->PsBranch;
                // $dataset['sales'][$q->name] = $q->sales;
                $sales[$q->PsBranch] = $q->PsSales;
                // $dataset['expenses'][$q->name] = $q->expenses;
                $expenses[$q->PsBranch] = $q->PsExpenses;
            }
            foreach ($labels as $key => $name) {
                if(!array_key_exists($key, $sales)){
                    $sales[$key] = 0;
                }
                if(!array_key_exists($key, $expenses)){
                    $expenses[$key] = 0;
                }
            }
            ksort($sales);
            ksort($expenses);
            $total_sales = array_sum($sales);
            $total_expenses = array_sum($expenses);

        //    session()->flash('success', 'نم جلب البيانات بنجاح ');
           toastr()->success('نم جلب البيانات بنجاح ');
           $notification=array(
            'message' => 'Successfully Done',
            'alert-type' => 'success'
        );
            return view('pages.home', compact('start_at','end_at','query','total_sales', 'total_expenses'));
        }catch (\Exception $ex){
            // session()->flash('error', 'لا توجد بيانات فى هذا التاريخ ');
            toastr()->error('لا توجد بيانات فى هذا التاريخ');
            return redirect()->back()->with($ex->getMessage());
        }

    }
    public function search(Request $request)
    {
        try{
            $start_at = \date($request->start_at);
            $end_at = \date($request->end_at);
            $query = PsHeader::select([DB::raw('PsBranch'), DB::raw('PsBranchCode'),  DB::raw('SUM(PsSales) as PsSales'), DB::raw('SUM(PsExpenses) as PsExpenses')])->whereBetween('PsDate', [$start_at .  ' 00:00:00', $end_at .  ' 23:59:59'])->orderBy('PsSales', 'DESC')->groupBy('PsBranch', 'PsBranchCode')->get();
            if($query == ""){
                return redirect()->back()->with('error', 'لا توجد بيانات فى هذا الوقت ');
            }
           $data['branches'][] = PsHeader::select(['PsBranch'])->whereBetween('PsDate', [$start_at, $end_at])->get();
           $data['sales'][] = PsHeader::select(['PsSales'])->whereBetween('PsDate', [$start_at, $end_at])->get();
           $data['expenses'][] = PsHeader::select(['PsExpenses'])->whereBetween('PsDate', [$start_at, $end_at])->get();
           $labels = [];
           $dataset = [];
           $sales = [];
           $expenses = [];
           foreach ($query as $q) {
               $labels['name'][] = $q->PsBranch;
               // $dataset['sales'][$q->name] = $q->sales;
               $sales[$q->PsBranch] = $q->PsSales;
               // $dataset['expenses'][$q->name] = $q->expenses;
               $expenses[$q->PsBranch] = $q->PsExpenses;
           }
           foreach ($labels as $key => $name) {
               if(!array_key_exists($key, $sales)){
                   $sales[$key] = 0;
               }
               if(!array_key_exists($key, $expenses)){
                   $expenses[$key] = 0;
               }
           }
           ksort($sales);
           ksort($expenses);
           $total_sales = array_sum($sales);
           $total_expenses = array_sum($expenses);

        //    session()->flash('success', 'نم جلب البيانات بنجاح ');
           toastr()->success('نم جلب البيانات بنجاح ');
            return view('pages.home', $data, compact('start_at','end_at','query','total_sales', 'total_expenses'));
        }catch (\Exception $ex){
            // session()->flash('error', 'لا توجد بيانات فى هذا التاريخ ');
            toastr()->error('لا توجد بيانات فى هذا التاريخ');
            return redirect()->back()->with($ex->getMessage());
        }
    }
    public function index2(Request $requst){
        try{
            $last_date = PsItemSpec1::select('PsDate')->orderBy('PsDate', 'desc')->first()->PsDate;
            $start_last_date_at = Carbon::parse($last_date)->format('Y/m/d');
            $end_last_date_at = Carbon::parse($last_date)->format('Y/m/d');
            $start_at = date($requst->start_at) ? date($requst->start_at) : $start_last_date_at;
            $end_at = date($requst->end_at) ? date($requst->end_at) :  $end_last_date_at;
            $categories= DB::table('item_spec1')->get();
            $branches= DB::table('branches')->get();
            $sale_category = $sum_category = $sale_branch = $sum_branch = [];

            for($i=1; $i<=count($categories); $i++){
                 array_push($sale_category, "SUM(CASE WHEN (Is1Id = $i) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales$i");
                 array_push($sum_category, "SUM(CASE WHEN (Is1Id = $i) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales$i");
            }
            for($i=1; $i<=count($branches); $i++){
                 array_push($sale_branch, "SUM(CASE WHEN (PsBranchCode = $i) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales$i");
                 array_push($sum_branch, "SUM(CASE WHEN (PsBranchCode = $i) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales$i");
            }
            $sale_category =implode(',', $sale_category);
            $sum_category =implode(',', $sum_category);
            $sale_branch =implode(',', $sale_branch);
            $sum_branch =implode(',', $sum_branch);

            $query = PsItemSpec1::select(['PsBranch', 'PsBranchCode', DB::raw($sale_category)])->whereBetween('PsDate', [$start_at .  ' 00:00:00', $end_at .  ' 23:59:59'])->groupBy('PsBranch', 'PsBranchCode')->get();
            $total_category = PsItemSpec1::select([DB::raw($sum_category)])->whereBetween('PsDate', [$start_at .  ' 00:00:00', $end_at .  ' 23:59:59'])->get();
            $total_branch = PsItemSpec1::select([DB::raw($sum_branch)])->whereBetween('PsDate', [$start_at .  ' 00:00:00', $end_at .  ' 23:59:59'])->get();
            $total_sales = PsItemSpec1::select([DB::raw('SUM(PsIs1Sales) as PsIs1ales')])->whereBetween('PsDate', [$start_at .  ' 00:00:00', $end_at .  ' 23:59:59'])->get();
           if($query == ""){
               return redirect()->back()->with('error', 'لا توجد بيانات فى هذا الوقت ');
           }
        //    session()->flash('success', 'نم جلب البيانات بنجاح ');
           toastr()->success('نم جلب البيانات بنجاح ');
           return view('pages.home2', compact('start_at', 'end_at', 'categories', 'branches', 'query', 'total_sales', 'total_category', 'total_branch'));
       }catch (\Exception $ex){
           session()->flash('error', 'لا توجد بيانات فى هذا التاريخ ');
           return redirect()->back()->with($ex->getMessage());
       }
    }
    public function search2(Request $request){
        try{

            $start_at = \date($request->start_at);
            $end_at = \date($request->end_at);
            $categories= DB::table('item_spec1')->get();
            $query = PsItemSpec1::select(['PsBranch', 'PsBranchCode', DB::raw('COUNT(PsCode) as PsCount'), DB::raw('SUM(CASE WHEN (Is1Id = 1) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales1'), DB::raw('SUM(CASE WHEN (Is1Id = 2) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales2'), DB::raw('SUM(CASE WHEN (Is1Id = 3) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales3'), DB::raw('SUM(CASE WHEN (Is1Id = 4) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales4'), DB::raw('SUM(CASE WHEN (Is1Id = 5) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales5'), DB::raw('SUM(CASE WHEN (Is1Id = 6) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales6'), DB::raw('SUM(CASE WHEN (Is1Id = 7) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales7'), DB::raw('SUM(CASE WHEN (Is1Id = 8) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales8'), DB::raw('SUM(CASE WHEN (Is1Id = 9) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales9'), DB::raw('SUM(CASE WHEN (Is1Id = 2) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales2'), DB::raw('SUM(CASE WHEN (Is1Id = 10) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales10'), ])->whereBetween('PsDate', [$start_at .  ' 00:00:00', $end_at .  ' 23:59:59'])->groupBy('PsBranch', 'PsBranchCode')->get();
            $total_category = PsItemSpec1::select([DB::raw('SUM(CASE WHEN (Is1Id = 1) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales1'), DB::raw('SUM(CASE WHEN (Is1Id = 2) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales2'), DB::raw('SUM(CASE WHEN (Is1Id = 3) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales3'), DB::raw('SUM(CASE WHEN (Is1Id = 4) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales4'), DB::raw('SUM(CASE WHEN (Is1Id = 5) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales5'), DB::raw('SUM(CASE WHEN (Is1Id = 6) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales6'), DB::raw('SUM(CASE WHEN (Is1Id = 7) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales7'), DB::raw('SUM(CASE WHEN (Is1Id = 8) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales8'), DB::raw('SUM(CASE WHEN (Is1Id = 9) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales9'), DB::raw('SUM(CASE WHEN (Is1Id = 2) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales2'), DB::raw('SUM(CASE WHEN (Is1Id = 10) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales10'), ])->whereBetween('PsDate', [$start_at .  ' 00:00:00', $end_at .  ' 23:59:59'])->get();
            $total_branch = PsItemSpec1::select([DB::raw('SUM(CASE WHEN (PsBranchCode = 1) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales1'), DB::raw('SUM(CASE WHEN (PsBranchCode = 2) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales2'), DB::raw('SUM(CASE WHEN (PsBranchCode = 3) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales3'), DB::raw('SUM(CASE WHEN (PsBranchCode = 4) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales4'), DB::raw('SUM(CASE WHEN (PsBranchCode = 5) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales5'), DB::raw('SUM(CASE WHEN (PsBranchCode = 6) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales6'), DB::raw('SUM(CASE WHEN (PsBranchCode = 7) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales7'), DB::raw('SUM(CASE WHEN (PsBranchCode = 8) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales8'), DB::raw('SUM(CASE WHEN (PsBranchCode = 9) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales9'), DB::raw('SUM(CASE WHEN (PsBranchCode = 10) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales10') ])->whereBetween('PsDate', [$start_at .  ' 00:00:00', $end_at .  ' 23:59:59'])->get();
            $total_sales = PsItemSpec1::select([DB::raw('SUM(PsIs1Sales) as PsIs1Sales')])->whereBetween('PsDate', [$start_at .  ' 00:00:00', $end_at .  ' 23:59:59'])->get();
            $branches= DB::table('branches')->get();
           if($query == ""){
                return redirect()->back()->with('error', 'لا توجد بيانات فى هذا الوقت ');
            }
            // session()->flash('success', 'نم جلب البيانات بنجاح ');
            toastr()->success('نم جلب البيانات بنجاح ');
            return view('pages.home2', compact('start_at', 'end_at','categories','branches', 'query', 'total_sales', 'total_category', 'total_branch'));
        }catch (\Exception $ex){
            // session()->flash('error', 'لا توجد بيانات فى هذا التاريخ ');
            toastr()->error('لا توجد بيانات فى هذا التاريخ');
            return redirect()->back()->with($ex->getMessage());
        }
    }
    public function index3(Request $request){
        try{

            $last_date = PsItemSpec1::select('PsDate')->orderBy('PsDate', 'desc')->first()->PsDate;
            $start_last_date_at = Carbon::parse($last_date)->format('Y/m/d');
            $end_last_date_at = Carbon::parse($last_date)->format('Y/m/d');
            $start_at = date($request->start_at) ? date($request->start_at) : $start_last_date_at;
            $end_at = date($request->end_at) ? date($request->end_at) :  $end_last_date_at;
             $branches = DB::table('branches')->where('brh_id', '!=', 0)->get();
            $categories= DB::table('item_spec1')->get();
            $query = PsItemSpec1::select([DB::raw('Is1Id'), DB::raw('Is1Name1'), DB::raw('Is1Id'), DB::raw('SUM(CASE WHEN (PsBranchCode = 1) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales1'), DB::raw('SUM(CASE WHEN (PsBranchCode = 2) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales2'), DB::raw('SUM(CASE WHEN (PsBranchCode = 3) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales3'), DB::raw('SUM(CASE WHEN (PsBranchCode = 4) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales4'), DB::raw('SUM(CASE WHEN (PsBranchCode = 5) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales5'), DB::raw('SUM(CASE WHEN (PsBranchCode = 6) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales6'), DB::raw('SUM(CASE WHEN (PsBranchCode = 7) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales7'), DB::raw('SUM(CASE WHEN (PsBranchCode = 8) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales8'), DB::raw('SUM(CASE WHEN (PsBranchCode = 9) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales9'), DB::raw('SUM(CASE WHEN (PsBranchCode = 10) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales10')])->whereBetween('PsDate', [$start_at .  ' 00:00:00', $end_at .  ' 23:59:59'])->groupBy('Is1Name1', 'Is1Id')->get();
            $total_category = PsItemSpec1::select([DB::raw('SUM(CASE WHEN (Is1Id = 1) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales1'), DB::raw('SUM(CASE WHEN (Is1Id = 2) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales2'), DB::raw('SUM(CASE WHEN (Is1Id = 3) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales3'), DB::raw('SUM(CASE WHEN (Is1Id = 4) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales4'), DB::raw('SUM(CASE WHEN (Is1Id = 5) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales5'), DB::raw('SUM(CASE WHEN (Is1Id = 6) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales6'), DB::raw('SUM(CASE WHEN (Is1Id = 7) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales7'), DB::raw('SUM(CASE WHEN (Is1Id = 8) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales8'), DB::raw('SUM(CASE WHEN (Is1Id = 9) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales9'), DB::raw('SUM(CASE WHEN (Is1Id = 2) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales2'), DB::raw('SUM(CASE WHEN (Is1Id = 10) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales10'), ])->whereBetween('PsDate', [$start_at .  ' 00:00:00', $end_at .  ' 23:59:59'])->get();
            $total_branch = PsItemSpec1::select([DB::raw('SUM(CASE WHEN (PsBranchCode = 1) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales1'), DB::raw('SUM(CASE WHEN (PsBranchCode = 2) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales2'), DB::raw('SUM(CASE WHEN (PsBranchCode = 3) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales3'), DB::raw('SUM(CASE WHEN (PsBranchCode = 4) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales4'), DB::raw('SUM(CASE WHEN (PsBranchCode = 5) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales5'), DB::raw('SUM(CASE WHEN (PsBranchCode = 6) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales6'), DB::raw('SUM(CASE WHEN (PsBranchCode = 7) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales7'), DB::raw('SUM(CASE WHEN (PsBranchCode = 8) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales8'), DB::raw('SUM(CASE WHEN (PsBranchCode = 9) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales9'), DB::raw('SUM(CASE WHEN (PsBranchCode = 10) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales10') ])->whereBetween('PsDate', [$start_at .  ' 00:00:00', $end_at .  ' 23:59:59'])->get();
            $total_sales = PsItemSpec1::select([DB::raw('SUM(PsIs1Sales) as PsIs1Sales')])->whereBetween('PsDate', [$start_at .  ' 00:00:00', $end_at .  ' 23:59:59'])->get();
           if($query == ""){
               return redirect()->back()->with('error', 'لا توجد بيانات فى هذا الوقت ');
           }
        //    session()->flash('success', 'نم جلب البيانات بنجاح ');
           toastr()->success('نم جلب البيانات بنجاح ');
           return view('pages.home3', compact('start_at','branches', 'categories', 'end_at','query', 'total_sales', 'total_branch', 'total_category'));
       }catch (\Exception $ex){
        //    session()->flash('error', 'لا توجد بيانات فى هذا التاريخ ');
           toastr()->error('لا توجد بيانات فى هذا التاريخ');
           return redirect()->back()->with($ex->getMessage());
       }
    }
    public function search3(Request $request){
        try{

            $branches = DB::table('branches')->get();
            $categories= DB::table('item_spec1')->get();
            $start_at = date($request->start_at);
            $end_at = date($request->end_at);
            $query = PsItemSpec1::select([DB::raw('Is1Id'), DB::raw('Is1Name1'), DB::raw('Is1Id'), DB::raw('SUM(CASE WHEN (PsBranchCode = 1) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales1'), DB::raw('SUM(CASE WHEN (PsBranchCode = 2) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales2'), DB::raw('SUM(CASE WHEN (PsBranchCode = 3) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales3'), DB::raw('SUM(CASE WHEN (PsBranchCode = 4) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales4'), DB::raw('SUM(CASE WHEN (PsBranchCode = 5) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales5'), DB::raw('SUM(CASE WHEN (PsBranchCode = 6) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales6'), DB::raw('SUM(CASE WHEN (PsBranchCode = 7) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales7'), DB::raw('SUM(CASE WHEN (PsBranchCode = 8) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales8'), DB::raw('SUM(CASE WHEN (PsBranchCode = 9) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales9'), DB::raw('SUM(CASE WHEN (PsBranchCode = 10) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales10')])->whereBetween('PsDate', [$start_at .  ' 00:00:00', $end_at .  ' 23:59:59'])->groupBy('Is1Name1', 'Is1Id')->get();
             $total_category = PsItemSpec1::select([DB::raw('SUM(CASE WHEN (Is1Id = 1) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales1'), DB::raw('SUM(CASE WHEN (Is1Id = 2) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales2'), DB::raw('SUM(CASE WHEN (Is1Id = 3) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales3'), DB::raw('SUM(CASE WHEN (Is1Id = 4) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales4'), DB::raw('SUM(CASE WHEN (Is1Id = 5) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales5'), DB::raw('SUM(CASE WHEN (Is1Id = 6) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales6'), DB::raw('SUM(CASE WHEN (Is1Id = 7) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales7'), DB::raw('SUM(CASE WHEN (Is1Id = 8) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales8'), DB::raw('SUM(CASE WHEN (Is1Id = 9) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales9'), DB::raw('SUM(CASE WHEN (Is1Id = 2) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales2'), DB::raw('SUM(CASE WHEN (Is1Id = 10) THEN (PsIs1Sales) ELSE 0 END)  as PsIs1Sales10'), ])->whereBetween('PsDate', [$start_at .  ' 00:00:00', $end_at .  ' 23:59:59'])->get();
              $total_branch = PsItemSpec1::select([DB::raw('SUM(CASE WHEN (PsBranchCode = 1) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales1'), DB::raw('SUM(CASE WHEN (PsBranchCode = 2) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales2'), DB::raw('SUM(CASE WHEN (PsBranchCode = 3) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales3'), DB::raw('SUM(CASE WHEN (PsBranchCode = 4) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales4'), DB::raw('SUM(CASE WHEN (PsBranchCode = 5) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales5'), DB::raw('SUM(CASE WHEN (PsBranchCode = 6) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales6'), DB::raw('SUM(CASE WHEN (PsBranchCode = 7) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales7'), DB::raw('SUM(CASE WHEN (PsBranchCode = 8) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales8'), DB::raw('SUM(CASE WHEN (PsBranchCode = 9) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales9'), DB::raw('SUM(CASE WHEN (PsBranchCode = 10) THEN (PsIs1Sales) ELSE 0 END)  as PsBranchCodeSales10') ])->whereBetween('PsDate', [$start_at .  ' 00:00:00', $end_at .  ' 23:59:59'])->get();
             $total_sales = PsItemSpec1::select([DB::raw('SUM(PsIs1Sales) as PsIs1Sales')])->whereBetween('PsDate', [$start_at .  ' 00:00:00', $end_at .  ' 23:59:59'])->get();

            if($query == ""){
                return redirect()->back()->with('error', 'لا توجد بيانات فى هذا الوقت ');
            }
            // session()->flash('success', 'نم جلب البيانات بنجاح ');
            toastr()->success('نم جلب البيانات بنجاح ');
            return view('pages.home3', compact('start_at', 'end_at','branches', 'categories','query', 'total_sales', 'total_category', 'total_branch'));
        }catch (\Exception $ex){
            // session()->flash('error', 'لا توجد بيانات فى هذا التاريخ ');
            toastr()->error('لا توجد بيانات فى هذا التاريخ');
            return redirect()->back()->with($ex->getMessage());
        }
    }
    public function index4(Request $request , $id){
        try{
             $last_date = PsItemSpec1::select('PsDate')->orderBy('PsDate', 'desc')->first()->PsDate;
            $start_last_date_at = Carbon::parse($last_date)->format('Y/m/d');
            $end_last_date_at = Carbon::parse($last_date)->format('Y/m/d');
            $start_at = date($request->start_at) ? date($request->start_at) : $start_last_date_at;
            $end_at = date($request->end_at) ? date($request->end_at) :  $end_last_date_at;
            $branch = DB::table('branches')->where('brh_id', $id)->first();
            $query = PsItemSpec1::select([DB::raw('Is1Name1'), DB::raw('SUM(PsIs1Sales)   as PsBranchCodeSales')])->where('PsBranchCode', $id)->whereBetween('PsDate', [$start_at .  ' 00:00:00', $end_at .  ' 23:59:59'])->groupBy('Is1Name1')->get();
            $total_category = PsItemSpec1::select([DB::raw('SUM(PsIs1Sales)  as PsIs1Sales') ])->where('PsBranchCode', $id)->whereBetween('PsDate', [$start_at .  ' 00:00:00', $end_at .  ' 23:59:59'])->get();

            // session()->flash('success', 'نم جلب البيانات بنجاح ');
            toastr()->success('نم جلب البيانات بنجاح ');
            return view('pages.home4', compact('start_at','end_at','query', 'branch', 'total_category'));

        }catch(Exception $ex){
            // session()->flash('error', 'لا توجد بيانات فى هذا التاريخ ');
            toastr()->error('لا توجد بيانات فى هذا التاريخ');
            return redirect()->back()->with($ex->getMessage());

        }
    }
    public function search4(Request $request , $id){
        try{
            $start_at = \date($request->start_at);
            $end_at = \date($request->end_at);
            $branch = DB::table('branches')->where('brh_id', $id)->first();
            $query = PsItemSpec1::select([DB::raw('Is1Name1'), DB::raw('SUM(PsIs1Sales)   as PsBranchCodeSales')])->where('PsBranchCode', $id)->whereBetween('PsDate', [$start_at .  ' 00:00:00', $end_at .  ' 23:59:59'])->groupBy('Is1Name1')->get();
            $total_category = PsItemSpec1::select([DB::raw('SUM(PsIs1Sales)  as PsIs1Sales') ])->where('PsBranchCode', $id)->whereBetween('PsDate', [$start_at .  ' 00:00:00', $end_at .  ' 23:59:59'])->get();
            toastr()->success('نم جلب البيانات بنجاح ');
            // session()->flash('success', 'نم جلب البيانات بنجاح ');
            return view('pages.home4', compact('start_at','end_at','query', 'branch', 'total_category'))->with('success', 'نم جلب البيانات بنجاح ');


        }catch(Exception $ex){
            // session()->flash('error', 'لا توجد بيانات فى هذا التاريخ ');
            toastr()->error('لا توجد بيانات فى هذا التاريخ');
            return redirect()->back()->with($ex->getMessage());
        }
    }
    public function index5(Request $request , $id){
        try{
            $last_date = PsItemSpec1::select('PsDate')->orderBy('PsDate', 'desc')->first()->PsDate;
            $start_last_date_at = Carbon::parse($last_date)->format('Y/m/d');
            $end_last_date_at = Carbon::parse($last_date)->format('Y/m/d');
            $start_at = date($request->start_at) ? date($request->start_at) : $start_last_date_at;
            $end_at = date($request->end_at) ? date($request->end_at) :  $end_last_date_at;
            $category = DB::table('item_spec1')->where('is1_id', $id)->first();
            $query = PsItemSpec1::select([DB::raw('PsBranch'), DB::raw('SUM(PsItemSpec1.PsIs1Sales)  as PsBranchCodeSales')])->where('Is1Id', $id)->whereBetween('PsDate', [$start_at .  ' 00:00:00', $end_at .  ' 23:59:59'])->groupBy('PsBranch')->get();
            $total_branch = PsItemSpec1::select([DB::raw('SUM(PsIs1Sales)  as PsIs1Sales') ])->where('Is1Id', $id)->whereBetween('PsDate', [$start_at .  ' 00:00:00', $end_at .  ' 23:59:59'])->get();
            // session()->flash('success', 'نم جلب البيانات بنجاح ');
            toastr()->success('نم جلب البيانات بنجاح ');
            return view('pages.home5', compact('start_at','end_at', 'category','query', 'total_branch'))->with('success', 'نم جلب البيانات بنجاح ');;

        }catch(Exception $ex){
            // session()->flash('error', 'لا توجد بيانات فى هذا التاريخ ');
            toastr()->error('لا توجد بيانات فى هذا التاريخ');
            return redirect()->back()->with($ex->getMessage());
        }
    }
    public function search5(Request $request , $id){
        try{
            $start_at = \date($request->start_at);
            $end_at = \date($request->end_at);
            $category = DB::table('item_spec1')->where('is1_id', $id)->first();
            $query = PsItemSpec1::select([DB::raw('PsBranch'), DB::raw('SUM(PsItemSpec1.PsIs1Sales)  as PsBranchCodeSales')])->where('Is1Id', $id)->whereBetween('PsDate', [$start_at .  ' 00:00:00', $end_at .  ' 23:59:59'])->groupBy('PsBranch')->get();
            $total_branch = PsItemSpec1::select([DB::raw('SUM(PsIs1Sales)  as PsIs1Sales') ])->where('Is1Id', $id)->whereBetween('PsDate', [$start_at .  ' 00:00:00', $end_at .  ' 23:59:59'])->get();

            // session()->flash('success', 'نم جلب البيانات بنجاح ');
            toastr()->success('نم جلب البيانات بنجاح ');
            return view('pages.home5', compact('start_at','end_at', 'category','query', 'total_branch'))->with('success', 'نم جلب البيانات بنجاح ');;

        }catch(Exception $ex){
            // session()->flash('error', 'لا توجد بيانات فى هذا التاريخ ');
            toastr()->error('لا توجد بيانات فى هذا التاريخ');
            return redirect()->back()->with($ex->getMessage());
        }
    }
    public function index6(Request $request, $id){
        try{
            $last_date = PsHeader::select('PsDate')->orderBy('PsDate', 'desc')->first()->PsDate;
            $start_last_date_at = Carbon::parse($last_date)->format('Y/m/d');
            $end_last_date_at = Carbon::parse($last_date)->format('Y/m/d');
            $start_at = date($request->start_at) ? date($request->start_at) : $start_last_date_at;
            $end_at = date($request->end_at) ? date($request->end_at) :  $end_last_date_at;
            $branch = DB::table('branches')->where('brh_id', $id)->first();
            $query = PsHeader::select([DB::raw('PsBranchCode'),DB::raw('PsBranch'),   DB::raw('SUM(PsSales) as PsSales'), DB::raw('SUM(PsExpenses) as PsExpenses')])->where('PsBranchCode', $id)->whereBetween('PsDate', [$start_at .  ' 00:00:00', $end_at .  ' 23:59:59'])->groupBy('PsBranch', 'PsBranchCode')->get();
             if($query == ""){
                 return redirect()->back()->with('error', 'لا توجد بيانات فى هذا الوقت ');
             }
            //  session()->flash('success', 'نم جلب البيانات بنجاح ');
             toastr()->success('نم جلب البيانات بنجاح ');
             return view('pages.home6', compact('start_at','end_at','query', 'branch'))->with('success', 'نم جلب البيانات بنجاح ');;

        }catch(Exception $ex){
            // session()->flash('error', 'لا توجد بيانات فى هذا التاريخ ');
            toastr()->error('لا توجد بيانات فى هذا التاريخ');
            return redirect()->back()->with($ex->getMessage());
        }
    }
    public function search6(Request $request, $id){
        try{
            $branch = DB::table('branches')->where('brh_id', $id)->first();
            $start_at = \date($request->start_at);
            $end_at = \date($request->end_at);
            $query = PsHeader::select([DB::raw('PsBranchCode'),DB::raw('PsBranch'),   DB::raw('SUM(PsSales) as PsSales'), DB::raw('SUM(PsExpenses) as PsExpenses')])->where('PsBranchCode', $id)->whereBetween('PsDate', [$start_at .  ' 00:00:00', $end_at .  ' 23:59:59'])->groupBy('PsBranch', 'PsBranchCode')->get();
             if($query == ""){
                 return redirect()->back()->with('error', 'لا توجد بيانات فى هذا الوقت ');
             }
            //  session()->flash('success', 'نم جلب البيانات بنجاح ');
             toastr()->success('نم جلب البيانات بنجاح ');
             return view('pages.home6', compact('start_at','end_at','query', 'branch'))->with('success', 'نم جلب البيانات بنجاح ');;

        }catch(Exception $ex){
            // session()->flash('error', 'لا توجد بيانات فى هذا التاريخ ');
            toastr()->error('لا توجد بيانات فى هذا التاريخ');
            return redirect()->back()->with($ex->getMessage());
        }
    }

    public function bar(Request $requst)
    {
        try {
            $last_date = PsHeader::select('PsDate')->orderBy('PsDate', 'desc')->first()->PsDate;
            $start_last_date_at = Carbon::parse($last_date)->format('Y/m/d');
            $end_last_date_at = Carbon::parse($last_date)->format('Y/m/d');
            $start_at = date($requst->start_at) ? date($requst->start_at) : $start_last_date_at;
            $end_at = date($requst->end_at) ? date($requst->end_at) :  $end_last_date_at;
            // session()->flash('success', 'نم جلب البيانات بنجاح ');
            toastr()->success('نم جلب البيانات بنجاح ');
        return view('modules.charts.bar', compact('start_at', 'end_at'))->with('success', 'نم جلب البيانات بنجاح ');;
        } catch (\Exception $ex) {
            // session()->flash('error', 'لا توجد بيانات فى هذا التاريخ ');
            toastr()->error('لا توجد بيانات فى هذا التاريخ');
            return redirect()->back()->with($ex->getMessage());
        }

    }
    public function doughnut(Request $requst)
    {
        try {
            $last_date = PsHeader::select('PsDate')->orderBy('PsDate', 'desc')->first()->PsDate;
        $start_last_date_at = Carbon::parse($last_date)->format('Y/m/d');
        $end_last_date_at = Carbon::parse($last_date)->format('Y/m/d');
        $start_at = date($requst->start_at) ? date($requst->start_at) : $start_last_date_at;
        $end_at = date($requst->end_at) ? date($requst->end_at) :  $end_last_date_at;
        // session()->flash('success', 'نم جلب البيانات بنجاح ');
        toastr()->success('نم جلب البيانات بنجاح ');
    return view('modules.charts.doughnut', compact('start_at', 'end_at'))->with('success', 'نم جلب البيانات بنجاح ');;
        } catch (\Throwable $th) {
            // session()->flash('error', 'لا توجد بيانات فى هذا التاريخ ');
            toastr()->error('لا توجد بيانات فى هذا التاريخ');
            return redirect()->back()->with($ex->getMessage());
        }

    }
    public function pie(Request $requst)
    {
        try {
        $last_date = PsHeader::select('PsDate')->orderBy('PsDate', 'desc')->first()->PsDate;
        $start_last_date_at = Carbon::parse($last_date)->format('Y/m/d');
        $end_last_date_at = Carbon::parse($last_date)->format('Y/m/d');
        $start_at = date($requst->start_at) ? date($requst->start_at) : $start_last_date_at;
        $end_at = date($requst->end_at) ? date($requst->end_at) :  $end_last_date_at;
        // session()->flash('success', 'نم جلب البيانات بنجاح ');
        toastr()->success('نم جلب البيانات بنجاح ');
        return view('modules.charts.pie', compact('start_at', 'end_at'))->with('success', 'نم جلب البيانات بنجاح ');;
        } catch (\Exception $ex) {
            // session()->flash('error', 'لا توجد بيانات فى هذا التاريخ ');
            toastr()->error('لا توجد بيانات فى هذا التاريخ');
            return redirect()->back()->with($ex->getMessage());
        }

    }
    public function chart(Request $requst, $start_date, $end_date){
        try{
            $last_date = PsHeader::select('PsDate')->orderBy('PsDate', 'desc')->first()->PsDate;
            $start_last_date_at = Carbon::parse($last_date)->format('Y/m/d');
            $end_last_date_at = Carbon::parse($last_date)->format('Y/m/d');
            $start_at = date($start_date) ? date($start_date) : $start_last_date_at;
            $end_at = date($end_date) ? date($end_date) :  $end_last_date_at;
             $query = PsHeader::select([DB::raw('PsBranch'),  DB::raw('SUM(PsSales) as PsSales'), DB::raw('SUM(PsExpenses) as PsExpenses')])->whereBetween('PsDate', [$start_at .  ' 00:00:00', $end_at .  ' 23:59:59'])->orderBy('PsSales', 'DESC')->groupBy('PsBranch')->get();
             if($query == ""){
                 return redirect()->back()->with('error', 'لا توجد بيانات فى هذا الوقت ');
             }
        $labels = [];
        $sales = [];
        $expenses = [];
        foreach ($query as $q) {
              $labels['name'][] = $q->PsBranch;
              $sales[$q->PsBranch] = $q->PsSales;
              $expenses[$q->PsBranch] = $q->PsExpenses;

        }
        // return ksort($sales);
        // ksort($expenses);
        toastr()->success('نم جلب البيانات بنجاح ');
        return [
            'labels' => array_values($labels['name']),
            'datasets' => [
            [
                'label' => 'Sales',
                'backgroundColor'=> 'rgba(236, 14, 34, 1)',
                'borderColor' => 'rgba(236, 14, 34, 1)',
                'borderWidth'=> 3,
                'data' => array_values($sales),
            ],
            [
                'label' => 'Expenses',
                'backgroundColor'=> 'rgba(23, 15, 252, 1)',
                'borderColor' =>  'rgba(23, 15, 252, 1)',
                'borderWidth'=> 3,
                'data' => array_values($expenses),
            ],

            ],
        ];
        }catch(Exception $ex){
            // session()->flash('error', 'لا توجد بيانات فى هذا التاريخ ');
            toastr()->error('لا توجد بيانات فى هذا التاريخ');
            return redirect()->back()->with($ex->getMessage());
        }
    }



}
