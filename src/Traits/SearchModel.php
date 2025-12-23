<?php

namespace SearchTable\Traits;

use SearchTable\Classes\Help;
use Illuminate\Support\Facades\Schema;

trait SearchModel
{
    public static function getTableFields():?array{
        return self::$table_fields ?? null;
    }
    
    public static function getModelAttributes():array{
        return Schema::getColumnListing((new self)->getTable());
    }
    
    public static function getModelKey():string{
        return (new self)->getKeyName();
    }
    
    public static function filter($filters, $sort = false){
        $query = $filters["query"] ?? "";
        $advanced = $filters["advanced_search"] ?? [];
        $modelfilter = $filters["modelfilter"] ?? [];
        $fields = self::getTableFields();
        
        $search = self::query();
        
        // Check model filter
        foreach($modelfilter as $key => $value){
            $search = $search->whereRaw("CONVERT(".($fields[$key]["custom-filter"] ?? $fields[$key]["key"] ?? "`".$key."`")." using 'utf8') = ?", $value);
        }
        
        foreach($fields as $key => $field){
            if(!empty($field["sort"])){
                $search = $search->orderByRaw(($field["custom-filter"] ?? "`".$key."`")." ".$field["sort"]);
            }
                
            if(!empty($advanced[$key])){
                $type = $field["advanced-type"] ?? "equal";
                
                if(($type) == "date-range"){
                    $dates = explode(" - ", $advanced[$key]);
                    
                    $search = $search->whereRaw(($field["custom-filter"] ?? "`".$key."`")." BETWEEN ? AND ?", [$dates[0], $dates[1] ?? $dates[0]]);
                }
                else if(($type) == "in-array"){
                    $multi_filter = [];
                    
                    foreach($advanced[$key] as $value){
                        $multi_filter[] = "CONVERT(".($field["custom-filter"] ?? "`".$key."`")." using 'utf8') LIKE '%".$value."%'";
                    }
                    
                    $search = $search->whereRaw("(".implode(" ".($advanced["filter_operators"][$key] ?? "AND")." ", $multi_filter).")");
                }
                else if(($type) == "like"){
                    $search = $search->whereRaw("CONVERT(".($field["custom-filter"] ?? "`".$key."`")." using 'utf8') LIKE '%".$advanced[$key]."%'");
                }
                else{
                    $multi_filter = [];
                    $multifilter_values = [];
                    
                    foreach($advanced[$key] as $value){
                        $multi_filter[] = "CONVERT(".($field["custom-filter"] ?? "`".$key."`")." using 'utf8') = ?";
                        $multifilter_values[] = $value;
                    }
                    
                    $search = $search->whereRaw("(".implode(" OR ", $multi_filter).")", $multifilter_values);
                }
            }
            
            if(!empty($field["filter"]) && !empty($query)){
                $filter[] = "CONVERT(".($field["custom-filter"] ?? "`".$key."`")." using 'utf8') LIKE ?";
                $filter_values[] = "%".$query."%";
            }
        }
        
        if(!empty($filter)){
            $search = $search->whereRaw("(".implode(" OR ", $filter).")", $filter_values);
        }
        
        return $search;
    }
}
