<?php
/**
 * Created by PhpStorm.
 * User: bacchu
 * Date: 9/12/19
 * Time: 12:56 PM
 */

namespace App\Http\Services;

use App\Jobs\UpdateCoinRateUsd;
use App\Model\AffiliationCode;
use App\Model\Buy;
use App\Model\Coin;
use App\Model\CurrencyList;
use App\Model\LangName;
use App\Model\Sell;
use App\Model\UserVerificationCode;
use App\Model\Wallet;
use App\Repository\AffiliateRepository;
use App\Repository\MarketRepository;
use App\Repository\OfferRepository;
use App\Services\Logger;
use App\Services\MailService;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Exception;

class AdminLangService
{

    public $response;
    function __construct()
    {

    }

    /**
     * @param $request
     * @return array
     */


    public function languageAddEdit($request,$auto = false){
        DB::beginTransaction();
        try {
            $response = isset($request->id) ? __("Language updated ") : __("Language created ") ;
            $id = $request->id ?? 0;
            $check = $auto ? [ 'code' => $request->code ] : [ 'id' => $id ] ;
            LangName::updateOrCreate($check,[
                'name' => $request->name,
                'key' => $request->key,
            ]);
        }catch (Exception $e){
            DB::rollBack();
            storeException($e,"Language Add Edit",$e->getMessage());
            return ["success" => false, "message" => $response . __("failed")];
        }
        DB::commit();
        return ["success" => true, "message" => $response . __("successfully")];
    }


    public function languageStatusUpdate($id){
        DB::beginTransaction();
        try{
            $c = LangName::find($id);
            $status = !$c->status;
            $c->update(['status' => $status]);
        }catch (\Exception $e){
            DB::rollBack();
            storeException($e,"Lang Status Changed",$e->getMessage());
            return false;
        }
        DB::commit();
        return true;
    }

}
