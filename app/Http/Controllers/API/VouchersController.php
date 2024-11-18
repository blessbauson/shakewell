<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\Voucher;

use App\Http\Requests\API\StoreVoucherRequest;
use App\Http\Requests\API\UpdateVoucherRequest;
use App\Http\Requests\API\GenerateVoucherRequest;

use App\Http\Resources\API\ApiResource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

use App\Traits\CodeGeneration;
use Carbon\Carbon;


class VouchersController extends Controller
{

    use CodeGeneration;

    public function index(Request $request)
    {
        $query = Voucher::listConditions($request)->distinct()->orderBy('id','ASC');

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


    public function generateVouchers(GenerateVoucherRequest $request)
    {
        $max_voucher_allowed =  config('api.max_voucher_count');
        $user = User::withCount('vouchers')->find($request->user_id);
       
        if($user){
            if(isset($user->vouchers_count) && $user->vouchers_count < $max_voucher_allowed){
                //generate voucher code
                $voucher_code = self::generateVoucherCode();
                $voucher_data = [
                    'user_id'           => $user->id,
                    'code'              => $voucher_code,
                    'created_by'        => $user->id,
                    'created_at'        => Carbon::now()
                ];
                Voucher::create($voucher_data);

                $user = User::withCount('vouchers')->find($user->id);
                return response(['status' => 'Success', 'message' => 'Voucher generated', 'voucher' => $voucher_code, 'user' => $user], 200);
            
            } else {
                return response(['status' => 'Error', 'message' => 'Max voucher count exceeded', 'user' => ''], 400);
            }

        } else {
            return response(['status' => 'Failed', 'message' => 'User not found', 'user' => ''], 400);
        }
    }


    public function store(StoreVoucherRequest $request)
    {
        $data = $request->all();
        $row  = Voucher::create($data);

        if($row){
            return (new ApiResource($row))->response()->setStatusCode(Response::HTTP_OK);
        } else {
            return (new JsonResource([]))->response()->setStatusCode(Response::HTTP_OK);
        }
    }


    public function update(UpdateVoucherRequest $request)
    {
        $data = $request->all();

        $row = Voucher::find($request->id);
        $row->update($data);

        if($row){
            return (new ApiResource($row))->response()->setStatusCode(Response::HTTP_OK);
        } else {
            return (new JsonResource([]))->response()->setStatusCode(Response::HTTP_OK);
        }
    }


    public function show(Voucher $voucher)
    {
        if($voucher){
            return (new ApiResource($voucher))->response()->setStatusCode(Response::HTTP_OK);
        } else {
            return (new JsonResource([]))->response()->setStatusCode(Response::HTTP_OK);
        }
    }


    public function destroy(Voucher $voucher)
    {
        $voucher->deleted_by = Auth::user()->id;
        $voucher->save();
        $voucher->delete();

        if($voucher){
            return (new ApiResource($voucher))->response()->setStatusCode(Response::HTTP_OK);
        } else {
            return (new JsonResource([]))->response()->setStatusCode(Response::HTTP_OK);
        }
    }
    
}
