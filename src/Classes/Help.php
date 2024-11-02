<?php

namespace SearchTable\Classes;

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
}