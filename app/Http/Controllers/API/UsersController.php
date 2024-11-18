<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\Voucher;

use App\Http\Requests\API\StoreUserRequest;
use App\Http\Requests\API\UpdateUserRequest;
use App\Http\Resources\API\ApiResource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

use App\Traits\CodeGeneration;
use Carbon\Carbon;

class UsersController extends Controller
{
    use CodeGeneration;
    public $voucher_char_count;


    public function __construct()
    {
        $this->voucher_char_count = config('api.voucher_chars_count');
    }


    public function index(Request $request)
    {
        $query = User::listConditions($request)
                    ->distinct()
                    ->orderBy('id','ASC');

        if ($request->has('per_page') && ! empty($request->per_page)) {
            $rows = $query->paginate($request->per_page ?? config('api.paginate_limit'));
        } else {
            $rows = $query->get();
        }

        if($rows){
            return (new ApiResource($rows))->response()->setStatusCode(Response::HTTP_OK);
        } else {
            return (new JsonResource([]))->response()->setStatusCode(Response::HTTP_OK);
        }
    }


    public function store(StoreUserRequest $request)
    {
        $data               = $request->all();
        $data['password']   = Hash::make($request->password);
        $data['created_at'] = date("Y-m-d H:i:s");

        $user = User::create($data);

        if($user && !empty($user->id)){

            //generate voucher code for every new creation of user
            $voucher_code = self::generateVoucherCode($this->voucher_char_count);
            $voucher_data = [
                'user_id'           => $user->id,
                'code'              => $voucher_code,
                'created_by'        => Auth::user()->id,
                'created_at'        => Carbon::now()
            ];
            Voucher::create($voucher_data);
        }

        if($user){
            return (new ApiResource($user))->response()->setStatusCode(Response::HTTP_OK);
        } else {
            return (new JsonResource([]))->response()->setStatusCode(Response::HTTP_OK);
        }
    }

    
    public function update(UpdateUserRequest $request)
    {
        $user = User::find($request->id);
        $data = $request->all();

        if (array_key_exists('password', $data) && ! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        if($user){
            return (new ApiResource($user))->response()->setStatusCode(Response::HTTP_OK);
        } else {
            return (new JsonResource([]))->response()->setStatusCode(Response::HTTP_OK);
        }
    }

    
    public function show(User $user)
    {
        $user->load(['vouchers']);
        if($user){
            return (new ApiResource($user))->response()->setStatusCode(Response::HTTP_OK);
        } else {
            return (new JsonResource([]))->response()->setStatusCode(Response::HTTP_OK);
        }
    }


    public function destroy(User $user)
    {
        $user->deleted_by = Auth::user()->id;
        $user->save();
        $user->delete();
        
        if($user){
            return (new ApiResource($user))->response()->setStatusCode(Response::HTTP_OK);
        } else {
            return (new JsonResource([]))->response()->setStatusCode(Response::HTTP_OK);
        }
    }

}
