<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Models\Voucher;
use DB;

trait CodeGeneration
{
    protected static function generateVoucherCode($codeLength = 5)
    {
        $characters         = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $charactersNumber   = strlen($characters);

        do {
            $code = '';
            while (strlen($code) < $codeLength) {
                $position   = rand(0, $charactersNumber - 1);
                $character  = $characters[$position];
                $code       = $code.$character;
            }

            $exists = Voucher::where('code', $code)->exists();

        } while ($exists);
        
        return $code;
    }


    protected static function randomPassword($charLength = 12)
    {
		$characters = preg_replace('/[^a-zA-Z0-9-_\.]/','',Carbon::now()->toDayDateTimeString());
	    $randomstr = '';
	    for ($i = 0; $i < $charLength; $i++) {
	        $randomstr .= $characters[rand(0, strlen($characters) - 1)];
	    }
	    return $randomstr.'!';
	}
}
