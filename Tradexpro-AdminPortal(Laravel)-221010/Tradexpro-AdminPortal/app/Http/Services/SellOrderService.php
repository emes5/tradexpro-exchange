<?php
namespace App\Http\Services;

use App\Http\Repositories\SellOrderRepository;
use App\Http\Repositories\UserWalletRepository;
use App\Model\FavouriteOrderBook;
use App\Model\Sell;
use App\User;
use App\Model\UserWallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SellOrderService extends BaseService
{
    public $model = Sell::class;
    public $repository = SellOrderRepository::class;
    public $logger = null;
    public $myCommonService;

    public function __construct()
    {
        parent::__construct($this->model,$this->repository);
        $this->myCommonService = new MyCommonService();
        $this->logger = app(Logger::class);

    }

    public function getOrders()
    {
        return $this->object->getOrders();
    }

    public function getAllOrders($base_coin_id, $trade_coin_id)
    {
        return $this->object->getAllOrders($base_coin_id, $trade_coin_id);
    }

    public function getAllOrderHistory($order_data=null)
    {
        $sell = Sell::where(['user_id' => Auth::id()])
            ->leftJoin( DB::raw('coins bc'), ['bc.id' => 'sells.base_coin_id'])
            ->leftJoin( DB::raw('coins tc'), ['tc.id' => 'sells.trade_coin_id'])
            ->where('amount', '>', 0)
            ->select('amount','processed','price','sells.status',
                DB::raw("bc.coin_type as base_coin, tc.coin_type as trade_coin, 'sell' as type,sells.created_at,sells.deleted_at"));
            if(!empty($order_data['column_name']) && !empty($order_data['order_by'])){
                $sell->orderBy($order_data['column_name'], $order_data['order_by']);
            }else{
                $sell->orderBy('sells.created_at', 'DESC');
            }
        $sell->withTrashed();
        return $sell;
    }

    public function getTotalAmount($base_coin_id, $trade_coin_id)
    {
        $response = $this->object->getTotalAmount($base_coin_id, $trade_coin_id);
        if (isset($response[0])) {
            $total = $response[0]->total;
        } else {
            $total = '0.00000000';
        }

        return $total;
    }

    public function create(Request $request)
    {
        $coinPairsService = new CoinPairService();
        $coinPairs = $coinPairsService->getDocs(['parent_coin_id' => $request->base_coin_id, 'child_coin_id' => $request->trade_coin_id ]);
        if(empty($coinPairs)){
            return [
                'status' => false,
                'message' => 'Invalid.sell.order.request!',
            ];
        }
        $user = Auth::check() ? Auth::user() : User::find($request->get('user_id'));

        // checking order type
        if (isset($request->is_market) && $request->is_market == 0) {
            $feesZero = isFeesZero(Auth::id(), $request->base_coin_id, $request->trade_coin_id, $request->amount, 'sell', $request->price);
            if($feesZero) {
                return [
                    'status' => false,
                    'message' =>  __('Minimum Sell Total Should Be ') . $feesZero
                ];
            }
            $settingTolerance = settings('trading_price_tolerance');
            // checking tolerance if the order category is limit.
            if (bccomp($settingTolerance, '0', 2) > 0) {
                $dashBoardService = new DashboardService();
                $price = $dashBoardService->getTotalVolume($request->base_coin_id, $request->trade_coin_id);
                $lastPrice = isset($price['sell_price']) ? $price['sell_price'] : $coinPairs[0]['price'];

                if ($lastPrice > 0) {
                    $tolerancePrice = bcdiv(bcmul($lastPrice, $settingTolerance), "100");
                    $highTolerance = bcadd($lastPrice, $tolerancePrice);
                    $lowTolerance = bcsub($lastPrice, $tolerancePrice);

                    if (bccomp($request->price, $highTolerance) > 0 || bccomp($request->price, $lowTolerance) < 0) {
                        return [
                            'status' => false,
                            'message' => __("The price must be between :lowTolerance and :highTolerance ", ['lowTolerance' => $lowTolerance, 'highTolerance' => $highTolerance])
                        ];
                    }
                }
            }
            return $this->_passiveSellOrder($request, $user->id);
        } else {
            $buyService = new BuyOrderService();
            $buys = $buyService->getDocs(['status' => 0, 'trade_coin_id' => $request->trade_coin_id, 'base_coin_id' => $request->base_coin_id, 'is_market' => 0]);

            if ($buys->isEmpty()) {
                return [
                    'status' => false,
                    'message' => __('Buy order not found for this sell order!'),
                ];
            }

            if ($request->get('category', 1) !== 13) {
                $feesZero = isFeesZero($user->id, $request->base_coin_id, $request->trade_coin_id, $request->amount, 'sell');
                if ($feesZero) {
                    return [
                        'status' => false,
                        'message' => __('Minimum Sell Amount Should Be ') . $feesZero
                    ];
                }
            }

            return $this->_activeSellOrder($request, $user->id);
        }
    }

    public function _passiveSellOrder(Request $request, $userId)
    {
        try {
            $response = false;
            // get sell wallet details
            DBService::beginTransaction();

            $walletRepository = new UserWalletRepository(UserWallet::class);
            $walletDetails = $walletRepository->getUserSingleWalletBalance($userId, $request->trade_coin_id);
            if (!$walletDetails) {
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => 'Invalid sell order request!',
                ];
            }
            // add and assigning maker and taker fees to the request
            $temporaryFees = calculated_fee_limit($userId);
            $request->merge([
                'maker_fees' => custom_number_format($temporaryFees['maker_fees']),
                'taker_fees' => custom_number_format($temporaryFees['taker_fees']),
                'btc_rate' => getBtcRate($request->trade_coin_id)
            ]);
            // calculate total amount

            $mainBalance = $walletDetails->balance;

            $totalSellCost = custom_number_format($request->amount);
            // checking if available balance is there
            if (bccomp($mainBalance, $totalSellCost) === -1) {
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => __('You need minimum balance  ') . $totalSellCost . ' ' . $walletDetails->coin_type,
                ];
            }

            $order = [
                'user_id' => $userId,
                'trade_coin_id' => $request->trade_coin_id,
                'base_coin_id' => $request->base_coin_id,
                'amount' => visual_number_format($request->get('amount')),
                'processed' => $request->get('processed', 0),
                'virtual_amount' => $request->get('amount') * random_int(20, 80) / 100,
                'price' => visual_number_format($request->get('price', 0)),
                'btc_rate' => $request->btc_rate,
                'is_market' => $request->get('is_market', 0),
                'maker_fees' => $request->maker_fees,
                'taker_fees' => $request->taker_fees,
                'is_conditioned' => $request->get('is_conditioned', 0),
            ];
            $response = $walletRepository->deductBalanceById($walletDetails, $totalSellCost);

            if($response == false){
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => __('Failed to place sell order!'),
                ];
            }
            if ($sell = $this->object->create($order)) {
                $request->merge([
                    'dashboard_type'=>'dashboard',
                    'order_type'=>'sell'
                ]);
                $this->myCommonService->sendNotificationToUserUsingSocket($userId,'Sell Market Order','Your market sell order placed successfully!');

                $d_service = new DashboardService();
                $this->logger->log("REQUEST CHECK", json_encode($request->all()));
                $socket_data = $d_service->getOrders($request)['data'];
                $channel_name = 'dashboard';
                $event_name = 'order_place';
                sendDataThroughWebSocket($channel_name,$event_name,$socket_data);
                $socket_data=[];
                $socket_data['sell_history'] = $d_service->getMyOrders($request)['data'];
                $request->merge(['order_type' => 'buy_sell']);
                $socket_data['open_orders'] = $d_service->getMyOrders($request)['data'];
                $channel_name = 'order_place_'.Auth::id();
                sendDataThroughWebSocket($channel_name,$event_name,$socket_data);
                $this->logger->log("NormalSellOrderPlace", "Sell Id: $sell->id Price: $sell->price Amount: $sell->amount");
                DBService::commit();
                broadcastOrderData($sell, 'sell', 'orderPlace');
                broadcastWalletData($walletDetails->wallet_id);

                $sell['type'] = 'sell';
                $sell['total'] = bcmul($sell->amount,$sell->price,8);
                $fees = 0;
                if($sell->maker_fees > $sell->taker_fees) {
                    $fees = bcmul(bcmul(bcmul(bcsub($sell->amount,$sell->processed,8),$sell->price,8), $sell->maker_fees,8),0.01,8);
                } else {
                    $fees = bcmul(bcmul(bcmul(bcsub($sell->amount,$sell->processed,8),$sell->price,8), $sell->taker_fees,8),0.01,8);
                }
                $sell['fees'] = $fees;
                return [
                    'status' => true,
                    'message' => __('Sell order is placed successfully!'),
                    'data' => $sell
                ];
            }
        }catch (\Exception $e){
            DBService::rollBack();

            return [
                'status' => false,
                'message' => __('Failed to place sell order!'),
            ];
        }
    }

    public function _activeSellOrder($request, $userId)
    {
        try {
            $response = false;

            // get sell wallet details
            DBService::beginTransaction();

            $walletRepository = new UserWalletRepository(UserWallet::class);
            $walletDetails = $walletRepository->getUserSingleWalletBalance($userId, $request->trade_coin_id);

            if (!$walletDetails) {
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => 'Invalid sell order request!',
                ];
            }

            $temporaryFees = calculated_fee_limit($userId);
            $request->merge([
                'maker_fees' => custom_number_format($temporaryFees['maker_fees']),
                'taker_fees' => custom_number_format($temporaryFees['taker_fees']),
                'btc_rate' => getBtcRate($request->trade_coin_id)
            ]);
            // calculate total amount
            $mainBalance = $walletDetails->balance;

            $totalSellCost = $request->amount;
            $totalSellCost = custom_number_format($totalSellCost);

            // checking if available balance is there
            if ((bccomp($mainBalance, $totalSellCost) === -1) && ($request->get('category', 1) !== 13)) {
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => __('You need minimum balance ') . $totalSellCost . ' ' . $walletDetails->coin_type,
                ];
            }

            $order = [
                'user_id' => $userId,
                'trade_coin_id' => $request->trade_coin_id,
                'base_coin_id' => $request->base_coin_id,
                'amount' => visual_number_format($request->get('amount')),
                'processed' => $request->get('processed', 0),
                'virtual_amount' => $request->get('amount') * random_int(20, 80) / 100,
                'price' => 0,
                'btc_rate' => $request->btc_rate,
                'is_market' => 1,
                'category' => $request->get('category', 1),
                'maker_fees' => $request->maker_fees,
                'taker_fees' => $request->taker_fees,
                'is_conditioned' => $request->get('is_conditioned', 0),
            ];
            //Deduct Amount from Main Balance
//            $response = getService(['method'=>'deductBalanceById','params'=>['user_id'=>$userId,'coin_id'=>$request->trade_coin_id,'amount'=>$totalSellCost]]);

            $response = $walletRepository->deductBalanceById($walletDetails, $totalSellCost);

            if($response == false){
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => __('Failed to place sell order!'),
                ];
            }
            if ($sell = $this->object->create($order)) {
                $this->logger->log("ActiveSellOrderPlace", "Sell Id: $sell->id Amount: $sell->amount");

                DBService::commit();
                broadcastWalletData($walletDetails->wallet_id);

                $this->myCommonService->sendNotificationToUserUsingSocket($userId,'Sell Limit Order','Your limit sell order placed successfully!');


                $request->merge([
                    'dashboard_type'=>'dashboard',
                    'order_type'=>'sell'
                ]);
                $d_service = new DashboardService();
                $this->logger->log("REQUEST CHECK", json_encode($request->all()));
                $socket_data = $d_service->getOrders($request)['data'];
                $channel_name = 'dashboard';
                $event_name = 'order_place';
                sendDataThroughWebSocket($channel_name,$event_name,$socket_data);
                $socket_data=[];
                $socket_data['sell_history'] = $d_service->getMyOrders($request)['data'];
                $request->merge(['order_type' => 'buy_sell']);
                $socket_data['open_orders'] = $d_service->getMyOrders($request)['data'];
                $channel_name = 'order_place_'.Auth::id();
                sendDataThroughWebSocket($channel_name,$event_name,$socket_data);

                return [
                    'status' => true,
                    'message' => __('Market sell order is placed successfully!'),
                    'data' => $sell
                ];
            }
        }catch (\Exception $e){
            DBService::rollBack();

            return [
                'status' => false,
                'message' => __('Failed to place sell order!'),
            ];
        }
    }

    public function createMultiSellOrder($request)
    {
        try {
            $userId = Auth::id();
            $response = false;
            // get Sell wallet details
            DBService::beginTransaction();

            $walletRepository = new UserWalletRepository(UserWallet::class);
            $walletDetails = $walletRepository->getUserSingleWalletBalance($userId, $request->trade_coin_id);
            if (!$walletDetails) {
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => 'Invalid sell order request!',
                ];
            }
            $temporaryFees = calculated_fee_limit($userId);
//            $request->request->add([
//                'maker_fees' => custom_number_format($temporaryFees['maker_fees']),
//                'taker_fees' => custom_number_format($temporaryFees['taker_fees']),
//                'btc_rate' => 0.005//custom_number_format($btcRate)
//            ]);
            $request->merge([
                'maker_fees' => custom_number_format($temporaryFees['maker_fees']),
                'taker_fees' => custom_number_format($temporaryFees['taker_fees']),
                'btc_rate' =>getBtcRate($request->trade_coin_id)
            ]);
            // calculate total amount

            $mainBalance = $walletDetails->balance;
            $inputAmount1 = $request->amount_1;
            $inputAmount2 = $request->amount_2;

            if (isset($request->price_3) && !empty($request->price_3) && isset($request->amount_3) && !empty($request->amount_3)) {
                $inputAmount3 = $request->amount_3;
            } else {
                $inputAmount3 = 0;
            }

            $inputTotal = bcadd($inputAmount1, bcadd($inputAmount2, $inputAmount3));
            $totalSellCost = custom_number_format($inputTotal);

            // checking if available balance is there
            if (bccomp($mainBalance, $totalSellCost) === -1) {
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => __('You need minimum balance(including fees): ') . $totalSellCost . ' ' . $walletDetails->coin_type,
                ];
            }

            $orders = [];
            $msg1 = $msg2 = $msg3 = "";
            $feesZero1 = $feesZero2 = $feesZero3 = 0;
            $currentTime = Carbon::now();
            if (isset($request->price_1) && !empty($request->price_1) && isset($request->amount_1) && !empty($request->amount_1)) {
                $feesZero1 = isFeesZero(Auth::id(), $request->base_coin_id, $request->trade_coin_id, $request->amount_1, 'sell', $request->price_1);
                if($feesZero1) {
                    $msg1 = __("Sell Total (" . bcmul($request->price_1,$request->amount_1) . ")  Should Not Less Than ") . $feesZero1;
                }
                $orders[] = [
                    'user_id' => $userId,
                    'trade_coin_id' => $request->trade_coin_id,
                    'base_coin_id' => $request->base_coin_id,
                    'amount' => visual_number_format($request->amount_1),
                    'virtual_amount' => $request->get('amount_1') * random_int(20, 80) / 100,
                    'price' => visual_number_format($request->price_1),
                    'btc_rate' => $request->btc_rate,
                    'maker_fees' => $request->maker_fees,
                    'taker_fees' => $request->taker_fees,
                    'created_at' => $currentTime,
                    'updated_at' => $currentTime
                ];
            }

            if (isset($request->price_2) && !empty($request->price_2) && isset($request->amount_2) && !empty($request->amount_2)) {
                $feesZero2 = isFeesZero(Auth::id(), $request->base_coin_id, $request->trade_coin_id, $request->amount_2, 'sell', $request->price_2);
                if($feesZero2) {
                    $msg2 = __("Sell Total (" . bcmul($request->price_2,$request->amount_2) . ")  Should Not Less Than ") . $feesZero2;
                }
                $orders[] = [
                    'user_id' => $userId,
                    'trade_coin_id' => $request->trade_coin_id,
                    'base_coin_id' => $request->base_coin_id,
                    'amount' => visual_number_format($request->amount_2),
                    'virtual_amount' => $request->get('amount_2') * random_int(20, 80) / 100,
                    'price' => visual_number_format($request->price_2),
                    'btc_rate' => $request->btc_rate,
                    'maker_fees' => $request->maker_fees,
                    'taker_fees' => $request->taker_fees,
                    'created_at' => $currentTime,
                    'updated_at' => $currentTime
                ];
            }

            if (isset($request->price_3) && !empty($request->price_3) && isset($request->amount_3) && !empty($request->amount_3)) {
                $feesZero3 = isFeesZero(Auth::id(), $request->base_coin_id, $request->trade_coin_id, $request->amount_3, 'sell', $request->price_3);
                if($feesZero3) {
                    $msg3 = __("Sell Total (" . bcmul($request->price_3,$request->amount_3) . ")  Should Not Less Than ") . $feesZero3;
                }
                $orders[] = [
                    'user_id' => $userId,
                    'trade_coin_id' => $request->trade_coin_id,
                    'base_coin_id' => $request->base_coin_id,
                    'amount' => visual_number_format($request->amount_3),
                    'virtual_amount' => $request->get('amount_3') * random_int(20, 80) / 100,
                    'price' => visual_number_format($request->price_3),
                    'btc_rate' => $request->btc_rate,
                    'maker_fees' => $request->maker_fees,
                    'taker_fees' => $request->taker_fees,
                    'created_at' => $currentTime,
                    'updated_at' => $currentTime
                ];
            }

            if (empty($orders)) {
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => __('No order to place'),
                ];
            }
            if($feesZero1 || $feesZero2 || $feesZero3){
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => $msg1 . "</br>" . $msg2 . "</br>" . $msg3,
                ];
            }
            //Deduct Amount from Main Balance
//            $response = getService(['method'=>'deductBalanceById','params'=>['user_id'=>$userId,'coin_id'=>$request->trade_coin_id,'amount'=>$totalSellCost]]);
            $response = $walletRepository->deductBalanceById($walletDetails, $totalSellCost);

            if($response == false){
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => __('Failed to place sell order!'),
                ];
            }

            if(isset($orders[0])) {
                if ($sell = $this->object->create($orders[0])) {
                    broadcastOrderData($sell, 'sell', 'orderPlace');
                    $this->logger->log("MultiSellOrderPlace", "Sell Details 1: Sell Id: $sell->id Price: $sell->price Amount: $sell->amount");
                }
            }
            if(isset($orders[1])) {
                if ($sell = $this->object->create($orders[1])) {
                    broadcastOrderData($sell, 'sell', 'orderPlace');
                    $this->logger->log("MultiSellOrderPlace", "Sell Details 2: Sell Id: $sell->id Price: $sell->price Amount: $sell->amount");
                }
            }
            if(isset($orders[2])) {
                if ($sell = $this->object->create($orders[2])) {
                    broadcastOrderData($sell, 'sell', 'orderPlace');
                    $this->logger->log("MultiSellOrderPlace", "Sell Details 3: Sell Id: $sell->id Price: $sell->price Amount: $sell->amount");
                }
            }

            DBService::commit();
            broadcastWalletData($walletDetails->wallet_id);

            return [
                'status' => true,
                'message' => __('Multi sell order is placed successfully!'),
            ];
        }catch (\Exception $e){
            DBService::rollBack();

            return [
                'status' => false,
                'message' => __('Failed to place sell order!'),
            ];
        }
    }

    public function getPrice($baseCoinId, $tradeCoinId)
    {
        return $this->object->getPrice($baseCoinId, $tradeCoinId);
    }

    public function getOnOrderBalance($baseCoinId, $tradeCoinId,$userId=null)
    {
        if($userId == null){
            $userId = Auth::id();
        }
        return $this->object->getOnOrderBalance($baseCoinId, $tradeCoinId,$userId);
    }

    public function getMyOrders($baseCoinId, $tradeCoinId, $userId)
    {
        return $this->object->getMyOrders($baseCoinId, $tradeCoinId, $userId);
    }

    /**
     * Insert Or Remove OrderBook as favorite
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function insertDeleteOrderBookFavorite($request){

        try{
            $obj = FavouriteOrderBook::where(['base_coin_id' => $request->base_coin_id,
                'trade_coin_id' => $request->trade_coin_id,
                'price' => $request->price, 'user_id' => DB::raw(Auth::id()),
                'type' => DB::raw("'sell'")])->first();
            if(is_null($obj)){
                $isOrder = Sell::where(['base_coin_id' => $request->base_coin_id,
                    'trade_coin_id' => $request->trade_coin_id,
                    'price' => $request->price])->first();
                if(empty($isOrder)){
                    return response()->json([
                        'status' => false,
                        'message' => __('order.not.found')
                    ]);
                }
                FavouriteOrderBook::create(['base_coin_id' => $request->base_coin_id,
                    'trade_coin_id' => $request->trade_coin_id,
                    'price' => $request->price,
                    'type' => 'sell',
                    'user_id' => DB::raw(Auth::id())]);
                broadcastPrivate('isFavoriteOrderBook',['base_coin_id' => $request->base_coin_id, 'trade_coin_id' => $request->trade_coin_id, 'price' => $request->price,'type' => 'sell','action' => 'add'], Auth::id());
                return response()->json([
                    'status' => true,
                    'message' => __('add.to.favorite')
                ]);
            }else{
                $obj->delete();
                broadcastPrivate('isFavoriteOrderBook',['base_coin_id' => $request->base_coin_id, 'trade_coin_id' => $request->trade_coin_id, 'price' => $request->price,'type' => 'sell','action' => 'remove'], Auth::id());
                return response()->json([
                    'status' => true,
                    'message' => __('remove.from.favorite')
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => __('failed.to.add.remove.from.favorite')
            ]);
        }

    }
}
