<?php

namespace SearchTable\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Facades\Excel;
use AdvancedModel\Exports\BaseExport;
use SearchTable\Classes\Help;

trait SearchController
{
    static public $model;
    
    public function index(Request $request){
        $class = "App\Models\\".str_replace("Controller", "", class_basename($this));
        
        return self::search_table($request, new $class);
    }
    
    private static function search_table(Request $request, $model){
        try {
            $modelfilter = Crypt::decryptString($request->modelfilter);
        } catch (\Throwable $th) {
            abort(400, __('search-table::search-table.model_filter_tampered'));
        }
        
        return Help::fragment("search-table::components.table", "search-table-body", [
            "model" => $model,
            "query" => $request->filter,
            "advanced" => $request->advanced_search,
            "page" => $request->page ?? 1,
            "limit" => $request->limit ?? null,
            "addRedirect" => $request->addRedirect ?? null,
            "modelfilter" => json_decode($modelfilter),
            "disableaddbutton" => $request->boolean("disableaddbutton") ?? false,
            "disablesearchbar" => $request->boolean("disablesearchbar") ?? false,
            "disabletotalrow" => $request->boolean("disabletotalrow") ?? false,
        ]);
    }
    
    public function export(Request $request){
        try {
            $modelfilter = Crypt::decryptString($request->modelfilter);
        } catch (\Throwable $th) {
            abort(400, __('search-table::search-table.model_filter_tampered'));
        }
        
        $class = "App\Models\\".str_replace("Controller", "", class_basename($this));
        
        $filters = [
            "query" => $request->filter,
            "advanced_search" => $request->advanced_search,
            "modelfilter" => json_decode($modelfilter, true),
        ];
        $models = $class::filter($filters)->get();
        
        dd($filters, $models);
        
        return response()->json([
            "base64" => base64_encode(Excel::raw(new BaseExport($models), \Maatwebsite\Excel\Excel::XLSX)),
            "filename" => "export-".date("YmdHis").".xlsx",
            "mimetype" => "application/vnd.ms-excel",
        ]);
    }
}