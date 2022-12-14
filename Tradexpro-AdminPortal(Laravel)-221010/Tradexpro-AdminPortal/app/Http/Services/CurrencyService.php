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

class CurrencyService
{

    public $response;
    function __construct()
    {

    }

    /**
     * @param $request
     * @return array
     */
    // marketplace data
    public function currencyList()
    {
        return CurrencyList::orderBy('id', 'desc')->get();
    }

    public function getActiveCurrencyList()
    {
        return CurrencyList::where('status',STATUS_ACTIVE)->orderBy('id', 'desc')->get();
    }

    public function currencyAddEdit($request,$auto = false){
        DB::beginTransaction();
        try {
            $response = isset($request->id) ? __("Currency updated ") : __("Currency created ") ;
            $id = $request->id ?? 0;
            $status =  isset($request->status) ? true : false;
            $check = $auto ? [ 'code' => $request->code ] : [ 'id' => $id ] ;
            CurrencyList::updateOrCreate($check,[
                'name' => $request->name,
                'code' => $request->code,
                'symbol' => $request->symbol,
                'rate' => $request->rate,
                'status' => $status,
            ]);
        }catch (Exception $e){
            DB::rollBack();
            storeException($e,"Currency Add Edit",$e->getMessage());
            return ["success" => false, "message" => $response . __("failed")];
        }
        DB::commit();
        return ["success" => true, "message" => $response . __("successfully")];
    }

    public function saveAllCurrency(){
        $currency = fiat_currency_array();
        $rates = $this->getCurrencyRateData();
        foreach ($rates['rates'] as $type => $rate){
            foreach ($currency as $index => $item){
                if($item['code'] == $type)
                    $currency[$index]['rate'] = $rate;
            }
        }
        foreach ($currency as $item){
            if(!isset($item['rate']))
                $item['rate'] = 1;
                $item['status'] = 1;
            $respose = $this->currencyAddEdit((object)$item, true);
        }
    }

    public function currencyStatusUpdate($id){
        DB::beginTransaction();
        try{
            $c = CurrencyList::find($id);
            $status = !$c->status;
            $c->update(['status' => $status]);
        }catch (\Exception $e){
            DB::rollBack();
            storeException($e,"Currency Status Changed",$e->getMessage());
            return false;
        }
        DB::commit();
        return true;
    }

    public function currencyRateSave(){
        $data = $this->getCurrencyRateData();
        DB::beginTransaction();
        try{
            foreach ($data['rates'] as $type => $rate)
                CurrencyList::where('code',$type)->update([ 'rate' => $rate ]);
        }catch (\Exception $e){
            storeException('currencyRateSave', $e->getMessage());
            DB::rollBack();
            $this->response = [ 'success' => false, 'message' => __('Currency Rate Update failed') ];
        }
        DB::commit();
        $this->response = [ 'success' => true, 'message' => __('Currency Rate Update') ];
    }
    public function getCurrencyRateData(){
        $headers = ['Content-Type: application/json'] ;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.exchangerate.host/latest?base=USD');
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result,true);
    }

    public function updateCoinRate(){
        try{
            $coins = Coin::where(['status' => STATUS_ACTIVE])->get();
           if(isset($coins[0])) {
               dispatch(new UpdateCoinRateUsd($coins));
           }
        }catch (\Exception $e){
            storeException("Update Coin Rate",$e->getMessage());
            return [ "success" => false, "message" => __("Coins rate updated Failed") ];
        }
        return [ "success" => true, "message" => __("Coins rate update process started successfully, It will take some time") ];
    }
}
