<?php

namespace App\Services;

class UtilService
{
    static public function GetStartCharacters($sentence, $is_all = true, $length = 1)
    {
        $words = explode(" ", $sentence);

        $start_char = '';

        for ($i = 0; $i < count($words); $i++) {
            if ($i < $length || $is_all) {
                $start_char .=  strtoupper($words[$i][0]);
            }
        }

        return $start_char;
    }
}
