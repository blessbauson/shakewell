<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\Voucher;

use App\Http\Requests\API\LoginRequest;
use App\Http\Requests\API\RegisterRequest;

use App\Http\Resources\API\ApiResource;
use Laravel\Passport\HasApiTokens;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

use App\Traits\CodeGeneration;
use App\Services\EmailService;
use App\Mail\NotificationMail;

use Carbon\Carbon;
use DB;
use Hash;

class AuthController extends Controller
{
    use CodeGeneration;
    protected $emailService;
    public $voucher_char_count;


    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
        $this->voucher_char_count = config('api.voucher_chars_count');
    }


    public function username()
    {
        return 'username';
    }


    public function login(LoginRequest $request)
    {
        $params     = $request->all();
        $username   = $request->username;
        $password   = $request->password;

        $user = User::where('username', '=', $username)->first();

        if($user){
            if(Hash::check($password, $user->password)){
                $token = $user->createToken('ShakewellToken')->accessToken;
                Auth::login($user);
                return response(['status' => 'Success', 'message' => 'Authenticated', 'access_token' => $token, 'authenticated_user' => $user], 200);
            } else {
                return response(['status' => 'Failed', 'message' => 'Invalid Username or Password. Please try again.', 'access_token' => '','authenticated_user' => ''], 400); 
            }
        } else {
            return response(['status' => 'Failed', 'message' => 'Invalid user' , 'access_token' => '', 'authenticated_user' => ''], 400);
        }
    }


    public function register(RegisterRequest $request)
    {
        $data = $request->all();
        
        if($request->password == $request->password_confirmation){
            $data['password']   = Hash::make($request->password);
            $data['created_at'] = Carbon::now();

            $user = User::create($data);
            if($user && !empty($user->id)){

                //generate voucher code
                $voucher_code = self::generateVoucherCode($this->voucher_char_count);
                $voucher_data = [
                    'user_id'           => $user->id,
                    'code'              => $voucher_code,
                    'created_by'        => $user->id,
                    'created_at'        => Carbon::now()
                ];
                Voucher::create($voucher_data);

                //send email
                if(!empty($user->email)){
                    $email_array = [
                        'subject'           => "Welcome Shakewell User",
                        'body'              => "Hi ".$user->first_name." ".$user->last_name.", <br/><br/> Your voucher code is <b>".$voucher_code."</b>.",
                        'email'             => $user->email,
                        'recipient_name'    => $user->first_name." ".$user->last_name
                    ];
                    $email_request = new Request($email_array);
                    $this->emailService->send_email($email_request);
                }

                $token = $user->createToken('ShakewellToken')->accessToken;
                return response(['status' => 'Success', 'message' => 'User is registered' , 'access_token' => $token, 'voucher' => $voucher_code, 'authenticated_user' => $user], 200);

            } else {
                return response(['status' => 'Failed', 'message' => 'Invalid user' , 'access_token' => '', 'authenticated_user' => ''], 400);
            }

        } else {
            return response(['status' => 'Failed', 'message' => 'Passwords dont match' , 'access_token' => '', 'authenticated_user' => ''], 400);
        }

        return response(['status' => 'Failed', 'message' => 'Error in Registration' , 'access_token' => '', 'authenticated_user' => ''], 400);
    }


    public function logout()
    {
        $auth_user = Auth::user();

        $refreshTokenRepository = app(\Laravel\Passport\RefreshTokenRepository::class);
        foreach(User::find($auth_user->id)->tokens as $token) {
            $token->revoke();
            $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($token->id);
        }
        
        return response(['message' => 'Logged Out'], 200);
    }


    public function sendEmail(Request $request)
    {
        $user = User::find($request->id);
        if($user && !empty($user->id)){

            //generate voucher code
            $voucher_code = self::generateVoucherCode($this->voucher_char_count);
          
            //send email
            if(!empty($user->email)){
                $email_array = [
                    'subject'           => "Welcome Shakewell User",
                    'body'              => "Your voucher code is <b>".$voucher_code."</b>.",
                    'email'             => $user->email,
                    'recipient_name'    => $user->first_name." ".$user->last_name
                ];
                $email_request = new Request($email_array);
                $this->emailService->send_email($email_request);
            }

            return response(['status' => 'Success', 'message' => 'Email sent!', 'voucher' => $voucher_code, 'authenticated_user' => $user], 200);

        } else {
            return response(['status' => 'Failed', 'message' => 'Error sending email' , 'access_token' => '', 'authenticated_user' => ''], 400);
        }
    }
}