<?php

namespace SearchTable\Classes;
use Illuminate\Support\Facades\View;

class Help
{
	// Converts date from "d/m/Y" to "Y-m-d"
	public static function convert_date(string $date):string{
		return implode("-", array_reverse(explode("/", $date)));
	}
	
	// Check if a dictionary is empty
	public static function empty_dictionary($dictionary){
        foreach($dictionary as $key => $value){
            if(!empty($value)) return false;
        }
        
        return true;
    }
	
	// Get view ragment
	public static function fragment(string $view, string $fragment, array $data = []):string{
		return View::make($view, array_merge($data, ["fragment" => true]))->fragment($fragment);
	}
}