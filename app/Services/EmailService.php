<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

use App\Mail\NotificationMail;

class EmailService 
{
    
    /**
    * Send Email Message
    * @return [array] $response
    */
    public function send_email($request)
    {
        try{
            $params = [];
            $params = [
                'name'          => $request->recipient_name,
                'email'         => $request->email,
                'body'          => $request->body,
                'view'          => 'defaultemail',
                'subject'       => $request->subject
            ];

			$mail = Mail::to($request->email)->send(new NotificationMail($params));
			return true;
            
		} catch(\Exception $e) {
			return false;
		}
    }

}