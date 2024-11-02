<?php

namespace App\Traits;

use SearchTable\Classes\Help;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

trait ControllerBase
{
    private static function modal_data($model, $data = []){
        return View::make("components.backoffice.modals.$model-data", $data);
    }
    
    private static function search_table(Request $request, $model){
        return Help::fragment("components.search-table", "search-table-body", [
            "model" => $model,
            "query" => $request->filter,
            "advanced" => $request->advanced_search,
            "page" => $request->page ?? 1,
            "modelfilter" => json_decode($request->modelfilter ?? "[]"),
            "disableaddbutton" => $request->boolean("disableaddbutton") ?? false,
            "disablesearchbar" => $request->boolean("disablesearchbar") ?? false,
            "disabletotalrow" => $request->boolean("disabletotalrow") ?? false,
        ]);
    }
}