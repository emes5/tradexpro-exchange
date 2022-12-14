<?php
namespace App\Http\Services;

use App\Http\Repositories\AdminCoinRepository;
use App\Model\Coin;
use App\Model\Wallet;
use Illuminate\Support\Facades\DB;

class CoinService extends BaseService {

    public $model = Coin::class;
    public $repository = AdminCoinRepository::class;

    public function __construct(){
        parent::__construct($this->model,$this->repository);
    }

    public function getCoin($data){
        $object = $this->object->getDocs($data);

        if (empty($object)) {
            return null;
        }

        return $object;
    }


    public function getPrimaryCoin()
    {
        $coinRepo = new AdminCoinRepository($this->model);
        $object = $this->object->getPrimaryCoin();

        return $object;
    }

    public function getBuyableCoin()
    {
        $object = $this->object->getBuyableCoin();
        if (empty($object)) {
            return null;
        }

        return json_encode($object);
    }

    public function getBuyableCoinDetails($coinId){
        $object = $this->object->getBuyableCoinDetails($coinId);
        if (empty($object)) {
            return null;
        }
        return json_encode($object);
    }

    public function generate_address($coinId)
    {
        $address='';

        $coinApiCredential = $this->object->getCoinApiCredential($coinId);
        if(isset($coinApiCredential)){
            //TODO Need to fix it
            $api = new BitCoinApiService($coinApiCredential->user, decryptId($coinApiCredential->password), $coinApiCredential->host, $coinApiCredential->port);
            $address = $api->getNewAddress();
        }

        return json_encode($address);
    }

    public function getCoinApiCredential($coinId){
        $coinRepo = new AdminCoinRepository($this->model);
        $object = $coinRepo->getCoinApiCredential($coinId);
        if (empty($object)) {
            return null;
        }
        return $object;
    }

    public function addCoin($data,$coin_id=null){
        try{

            if(!empty($coin_id)){
                $coinData = Coin::find($coin_id);
                if ($coinData->coin_type != $data['coin_type']) {
                    if ($coinData->coin_type == 'BTC' || $coinData->coin_type == 'USDT') {
                        return ['success'=>false,'data' => "",'message'=> __('You can not change this coin, because this is on of the base coin')];
                    }
                    $checkType = checkCoinTypeUpdateCondition($coinData);
                    if ($checkType['success'] == false) {
                        return ['success'=>false,'data' => "",'message'=> $checkType['message']];
                    }
                }
                if ($coinData->network != $data['network']) {
                    $checkNetwork = checkCoinNetworkUpdateCondition($coinData);
                    if ($checkNetwork['success'] == false) {
                        return ['success'=>false,'data' => "",'message'=> $checkNetwork['message']];
                    }
                }
                $coin = $this->object->updateCoin($coin_id,$data);
                if ($coinData->coin_type != $data['coin_type']) {
                    Wallet::where(['coin_id' => $coinData->id])->update(['coin_type' => $data['coin_type'], 'name' => $data['coin_type'].' wallet']);
                }
            }else{
//                if (empty($data['coin_icon'])) {
//                    return ['success' => false, 'message' => 'Coin icon can not be empty.'];
//                }
                $coin = $this->object->addCoin($data);
            }

            return ['success'=>true,'data'=>$coin,'message'=>__('updated successful.')];
        } catch(\Exception $e) {
            return ['success'=>false,'data'=>null,'message'=>'something.went.wrong'];
        }
    }

    public function getCoinDetailsById($coinId){
        try{
            $coin = $this->object->getCoinDetailsById($coinId);
            if($coin) {
                return ['success'=>true,'data'=>$coin,'message'=>'successfull.'];
            } else {
                return ['success'=>false,'data'=>'','message'=>__('Data not found')];
            }
        }catch(\Exception $e){
            return ['success'=>false,'data'=>null,'message'=>'something.went.wrong'];
        }
    }

    // admin coin delete
    public function adminCoinDeleteProcess($coinId)
    {
        $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        DB::beginTransaction();
        try {
            $coin = Coin::find($coinId);
            if ($coin) {
                if ($coin->coin_type == 'BTC' || $coin->coin_type == 'USDT') {
                    return ['success' => false, 'message' => __('You never delete this coin, because this is on of the base coin '), 'data' => []];
                }
                $check = checkCoinDeleteCondition($coin);
                if ($check['success'] == true) {
                    $coin->delete();
                    Wallet::where(['coin_id' => $coin->id])->delete();
                    $response = ['success' => true, 'message' => __('Coin deleted successfully'), 'data' => []];
                } else {
                    $response = ['success' => false, 'message' => $check['message'], 'data' => []];
                }
            } else {
                $response = ['success' => false, 'message' => __('Coin not found'), 'data' => []];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            storeException('adminCoinDeleteProcess', $e->getMessage());
        }
        DB::commit();
        return $response;
    }
}
