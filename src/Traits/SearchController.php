<?php

namespace SearchTable\Traits;

use SearchTable\Classes\Help;
use Illuminate\Http\Request;

trait SearchController
{
    static public $model;
    
    public function index(Request $request){
        $class = "App\Models\\".str_replace("Controller", "", class_basename($this));
        
        return self::search_table($request, new $class);
    }
    
    private static function search_table(Request $request, $model){
        return Help::fragment("search-table::components.table", "search-table-body", [
            "model" => $model,
            "query" => $request->filter,
            "advanced" => $request->advanced_search,
            "page" => $request->page ?? 1,
            "limit" => $request->limit ?? null,
            "addRedirect" => $request->addRedirect ?? null,
            "modelfilter" => json_decode($request->modelfilter ?? "[]"),
            "disableaddbutton" => $request->boolean("disableaddbutton") ?? false,
            "disablesearchbar" => $request->boolean("disablesearchbar") ?? false,
            "disabletotalrow" => $request->boolean("disabletotalrow") ?? false,
        ]);
    }
}