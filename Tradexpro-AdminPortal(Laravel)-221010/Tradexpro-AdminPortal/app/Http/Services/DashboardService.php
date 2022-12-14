<?php


namespace App\Http\Services;

use App\Http\Repositories\BuyOrderRepository;
use App\Http\Repositories\CoinPairRepository;
use App\Http\Repositories\DashboardRepository;
use App\Http\Repositories\SellOrderRepository;
use App\Http\Repositories\UserWalletRepository;
use App\Model\Buy;
use App\Model\CoinPair;
use App\Model\SelectedCoinPair;
use App\Model\Sell;
use App\Model\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardService
{
    public $repository;

    public function __construct()
    {
        $this->repository = new DashboardRepository();
    }
    public function dashboard($userId = null)
    {
        $data['status'] = true;
        $data['message'] = '';
        if (empty($userId)) {
            $userId = Auth::id();
        }
        try {
            $data['data']['withdrawal'] = Withdrawal::select('withdrawals.*', 'coins.coin_type', 'coins.full_name', 'coins.coin_icon')
                ->join('user_wallets', 'withdrawals.user_wallet_id', '=', 'user_wallets.id')
                ->join('coins', 'user_wallets.coin_id', '=', 'coins.id')
                ->doesnthave('systemWithdrawalAdjustment')
                ->whereHas('userWallet', function ($query) use ($userId) {
                    $query->whereHas('user', function ($where) use ($userId) {
                        $where->where(['user_id' => $userId]);
                    });
                })->where('status', 1)
                ->orderBy('id', 'desc')
                ->first();
            if (!empty($data['data']['withdrawal']) && $data['data']['withdrawal']->coin_icon) {
                $data['data']['withdrawal']->coin_icon = getImageUrl(coinIconPath() . $data['data']['withdrawal']->coin_icon);
            }
            $data['data']['deposit'] = Deposit::select('deposits.*', 'coins.coin_type', 'coins.full_name', 'coins.coin_icon', 'user_wallets.address')
                ->join('user_wallets', 'deposits.user_wallet_id', '=', 'user_wallets.id')
                ->join('coins', 'user_wallets.coin_id', '=', 'coins.id')
                ->doesnthave('systemDepositAdjustment')
                ->whereHas('userWallet', function ($query) use ($userId) {
                    $query->whereHas('user', function ($where) use ($userId) {
                        $where->where(['user_id' => $userId]);
                    });
                })->orderBy('id', 'desc')
                ->first();
            if (!empty($data['data']['deposit']) && $data['data']['deposit']->coin_icon) {
                $data['data']['deposit']->coin_icon = getImageUrl(coinIconPath() . $data['data']['deposit']->coin_icon);
            }
            $data['data']['currencyDeposit'] = CurrencyDeposit::select('currency_deposits.*', 'coins.coin_type', 'coins.full_name', 'coins.coin_icon')
                ->join('user_wallets', 'currency_deposits.user_wallet_id', '=', 'user_wallets.id')
                ->join('coins', 'user_wallets.coin_id', '=', 'coins.id')
                ->whereHas('userWallet', function ($query) use ($userId) {
                    $query->whereHas('user', function ($where) use ($userId) {
                        $where->where(['user_id' => $userId]);
                    });
                })->where('status', 1)
                ->orderBy('id', 'desc')
                ->first();
            if (!empty($data['data']['currencyDeposit']) && $data['data']['currencyDeposit']->coin_icon) {
                $data['data']['currencyDeposit']->coin_icon = getImageUrl(coinIconPath() . $data['data']['currencyDeposit']->coin_icon);
            }
            $data['data']['currencyWithdrawal'] = null;
            $data['data']['transfer'] = BalanceTransferHistory::select('balance_transfer_histories.*', 'coins.coin_type', 'coins.full_name', 'coins.coin_icon')
                ->join('user_wallets', 'balance_transfer_histories.user_wallet_id', '=', 'user_wallets.id')
                ->join('coins', 'user_wallets.coin_id', '=', 'coins.id')
                ->whereHas('userWallet', function ($query) use ($userId) {
                    $query->whereHas('user', function ($where) use ($userId) {
                        $where->where(['user_id' => $userId]);
                    });
                })->orderBy('id', 'desc')
                ->first();
            if (!empty($data['data']['transfer']) && $data['data']['transfer']->coin_icon) {
                $data['data']['transfer']->coin_icon = getImageUrl(coinIconPath() . $data['data']['transfer']->coin_icon);
            }
            $response = $this->getPreferredCurrencyWalletData($userId);
            if ($response['status']) {
                $data['data']['wallets'] = $response['data']['wallets'];
                $data['data']['totalBalance'] = $response['data']['totalBalance'];
                $data['data']['preferredCurrency'] = $response['data']['preferredCurrency'];
                $data['data']['preferredCurrencyIcon'] = $response['data']['preferredCurrencyIcon'];
            } else {
                return [
                    'status' => false,
                    'message' => 'something.went.wrong ' . $response['message'],
                    'data' => []
                ];
            }

            return $data;
        } catch (\Exception $exception) {
            return [
                'status' => false,
                'message' => 'something.went.wrong ' . $exception->getMessage() . ' ' . $exception->getLine(),
                'data' => []
            ];
        }
    }


    public function _getTradeCoin()
    {
        $repo = new DashboardRepository();
        $selectedCoinPair = $repo->getDocs(['user_id' => getUserId()])->first();

        if(!empty($selectedCoinPair)){
            return $selectedCoinPair->trade_coin_id;
        }else{
            return 1;
        }
    }

    public function _getBaseCoin()
    {
        $repo = new DashboardRepository();
        $selectedCoinPair = $repo->getDocs(['user_id' => getUserId()])->first();
        if(!empty($selectedCoinPair)){
            return $selectedCoinPair->base_coin_id;
        }else{
            return 2;
        }
    }


    public function getAllCoinPairs()
    {
        $response = [
            'status' => false,
            'message' =>__('Data not found'),
            'data' => []
        ];
        try {
            $repo = new CoinPairRepository(CoinPair::class);
            $response = [
                'status' => true,
                'message' =>__('Data get successfully'),
                'data' => $repo->getAllCoinPairs()
            ];
            return $response;
        } catch (\Exception $e) {
            Log::info('get all coin pairs exception -> '.$e->getMessage());
            return $response;
        }
    }

    public function getCoinPair($baseCoinId, $tradeCoinId)
    {
        if (empty($tradeCoinId) || empty($baseCoinId)) {
            $tradeCoinId = $this->_getTradeCoin();
            $baseCoinId = $this->_getBaseCoin();
        } else {
            $this->_setTradeCoin($tradeCoinId);
            $this->_setBaseCoin($baseCoinId);
        }

        $repo = new CoinPairRepository(CoinPair::class);

        return $repo->getCoinPairsData($baseCoinId, $tradeCoinId);
    }

    public function _setTradeCoin($tradeCoinId)
    {
        $repo = new DashboardRepository();
        $selectedCoinPair = $repo->getDocs(['user_id' => getUserId()])->first();
        if(!empty($selectedCoinPair)){
            return $repo->updateWhere(['user_id' => getUserId()],['trade_coin_id' => $tradeCoinId]);
        }else{
            return SelectedCoinPair::create(['user_id' => getUserId(),'trade_coin_id' => 1, 'base_coin_id' => 2]);
        }
    }

    public function _setBaseCoin($baseCoinId)
    {
        $repo = new DashboardRepository();
        $selectedCoinPair = $repo->getDocs(['user_id' => getUserId()])->first();
        if(!empty($selectedCoinPair)){
            return $repo->updateWhere(['user_id' => getUserId()],['base_coin_id' => $baseCoinId]);
        }else{
            return SelectedCoinPair::create(['user_id' => getUserId(),'trade_coin_id' => 1, 'base_coin_id' => 2]);
        }
    }

    public function getLastPriceList($baseCoinId = null, $tradeCoinId = null)
    {
        if($baseCoinId==null && $tradeCoinId == null){
            return \App\Model\CoinPair::orderBy('created_at','desc');
        }elseif ($baseCoinId != null && $tradeCoinId == null){
            return CoinPair::where(['parent_coin_id'=> $baseCoinId])->orderBy('created_at','desc');
        }elseif ($baseCoinId == null && $tradeCoinId != null){
            return CoinPair::where(['child_coin_id'=> $tradeCoinId])->orderBy('created_at','desc');
        }elseif ($baseCoinId != null && $tradeCoinId != null){
            return CoinPair::where(['parent_coin_id'=> $baseCoinId,'child_coin_id'=> $tradeCoinId])->orderBy('created_at','desc');
        }

    }




    public function getOnOrderBalance($baseCoinId, $tradeCoinId)
    {
        $data['total_buy'] = $this->repository->getOnOrderBalance($baseCoinId);
        $data['total_sell'] = $this->repository->getOnOrderBalance($tradeCoinId);

        return $data;
    }

    public function getTotalVolume($baseCoinId, $tradeCoinId)
    {
        $buyOrderService = new BuyOrderService();
        $data['total_buy_amount'] = visual_number_format($buyOrderService->getTotalAmount($baseCoinId, $tradeCoinId));
        $data['buy_price'] = visual_number_format($buyOrderService->getPrice($baseCoinId, $tradeCoinId));
        $sellOrderService = new SellOrderService();
        $data['total_sell_amount'] = visual_number_format($sellOrderService->getTotalAmount($baseCoinId, $tradeCoinId));
        $data['sell_price'] = visual_number_format($sellOrderService->getPrice($baseCoinId, $tradeCoinId));

        return $data;
    }

    // get order data

    public function getOrderData($request)
    {
        $response = [
            'status' => false,
            'message' => __('Something went wrong'),
            'data' => []
        ];
        $baseCoinId = $request->base_coin_id;
        $tradeCoinId = $request->trade_coin_id;
        try {
            if(Auth::guard('api')->check())  {
                if (empty($baseCoinId) || empty($tradeCoinId)) {

                    $tradeCoinId = $this->_getTradeCoin();
                    $baseCoinId = $this->_getBaseCoin();

                    $data['base_coin_id'] = $baseCoinId;
                    $data['trade_coin_id'] = $tradeCoinId;
                } else {
                    $data['base_coin_id'] = $baseCoinId;
                    $data['trade_coin_id'] = $tradeCoinId;
                }
                $baseCoinData = $this->getCoinPair($baseCoinId, $tradeCoinId);

                $data['base_coin_id'] = $baseCoinData->parent_coin_id;
                $data['trade_coin_id'] = $baseCoinData->child_coin_id;
                $data['total']['trade_wallet']['balance'] = $baseCoinData->balance;
                $data['total']['trade_wallet']['coin_type'] = $baseCoinData->child_coin_name;
                $data['total']['trade_wallet']['full_name'] = $baseCoinData->child_full_name;
                $data['total']['trade_wallet']['high'] = $baseCoinData->high;
                $data['total']['trade_wallet']['low'] = $baseCoinData->low;
                $data['total']['trade_wallet']['volume'] = $baseCoinData->volume;
                $data['total']['trade_wallet']['last_price'] = $baseCoinData->last_price;
                $data['total']['trade_wallet']['price_change'] = $baseCoinData->price_change;

                $walletService = new UserWalletService();
                $wallet = $walletService->getBalance(getUserId(), $baseCoinData->parent_coin_id);

                $data['total']['base_wallet']['balance'] = json_decode($wallet)->balance;
                $data['total']['base_wallet']['coin_type'] = $baseCoinData->parent_coin_name;
                $data['total']['base_wallet']['full_name'] = $baseCoinData->parent_full_name;

                $data['fees'] = calculated_fee_limit(getUserId());
                $onOrder = $this->getOnOrderBalance($baseCoinId, $tradeCoinId);
                $data['on_order']['trade_wallet'] = $onOrder['total_sell'];
                $data['on_order']['base_wallet'] = $onOrder['total_buy'];

                $price = $this->getTotalVolume($baseCoinId, $tradeCoinId);
                $data['sell_price'] = $price['sell_price'] > 0 ? $price['sell_price'] : $baseCoinData->last_price;
                $data['buy_price'] = $price['buy_price'] > 0 ? $price['buy_price'] : $baseCoinData->last_price;

            } else {
                if (empty($tradeCoinId) || empty($baseCoinId)) {
                    $tradeCoinId = 1;
                    $baseCoinId = 2;
                }
                $repo = new CoinPairRepository(CoinPair::class);
                $baseCoinData = $repo->getCoinPairsData($baseCoinId, $tradeCoinId);

                $data['base_coin_id'] = $baseCoinData->parent_coin_id;
                $data['trade_coin_id'] = $baseCoinData->child_coin_id;
                $data['total']['trade_wallet']['balance'] = $baseCoinData->balance;
                $data['total']['trade_wallet']['coin_type'] = $baseCoinData->child_coin_name;
                $data['total']['trade_wallet']['full_name'] = $baseCoinData->child_full_name;
                $data['total']['trade_wallet']['high'] = $baseCoinData->high;
                $data['total']['trade_wallet']['low'] = $baseCoinData->low;
                $data['total']['trade_wallet']['volume'] = $baseCoinData->volume;
                $data['total']['trade_wallet']['last_price'] = $baseCoinData->last_price;
                $data['total']['trade_wallet']['price_change'] = $baseCoinData->price_change;

                $data['total']['base_wallet']['balance'] = 0;
                $data['total']['base_wallet']['coin_type'] = $baseCoinData->parent_coin_name;
                $data['total']['base_wallet']['full_name'] = $baseCoinData->parent_full_name;

                $data['fees'] = 0;
                $data['on_order']['trade_wallet'] = 0;
                $data['on_order']['base_wallet'] = 0;

                $price = $this->getTotalVolume($baseCoinId, $tradeCoinId);
                $data['sell_price'] = $price['sell_price'] > 0 ? $price['sell_price'] : $baseCoinData->last_price;
                $data['buy_price'] = $price['buy_price'] > 0 ? $price['buy_price'] : $baseCoinData->last_price;

            }
            $data['base_coin'] = get_coin_type($data['base_coin_id']);
            $data['trade_coin'] = get_coin_type($data['trade_coin_id']);
            $data['exchange_pair'] =$data['trade_coin'].'_'.$data['base_coin'];
            $data['exchange_coin_pair'] =$data['trade_coin'].'/'.$data['base_coin'];

            $response = [
                'status' => true,
                'message' => __('Data get successfully'),
                'data' => $data
            ];

            return $response;
        } catch (\Exception $exception) {
            Log::info('get order data exception--> '. $exception->getMessage());
            return [
                'status' => false,
                'message' => __('Something went wrong. Please try again!'.getError($exception)),
                'data' => []
            ];
        }
    }

    // get all orders
    public function getOrders($request)
    {
        $response = [
            'status' => false,
            'message' => __('Something went wrong'),
            'data' => []
        ];

        try {
            $setting_per_page = isset(allsetting()['user_pagination_limit']) ? allsetting()['user_pagination_limit'] : 50;
            $perPage = empty($request->per_page) ? $setting_per_page : $request->per_page;

            $volume = $this->getTotalVolume($request->base_coin_id, $request->trade_coin_id);
            if ($request->order_type == 'sell') {
                $sellOrderService = new SellOrderService();
                if(isset($request->dashboard_type) && $request->dashboard_type == 'dashboard') {
                    $data['orders'] = $sellOrderService->getAllOrders($request->base_coin_id, $request->trade_coin_id)->limit($perPage)->get();
                } else {
                    $data['orders'] = $sellOrderService->getAllOrders($request->base_coin_id, $request->trade_coin_id);
                }
                $data['order_type'] = 'sell';
                $data['total_volume'] = $volume['total_sell_amount'];
                $response = [
                    'status' => true,
                    'message' => '',
                    'data' => $data
                ];
            } else if ($request->order_type == 'buy') {
                $buyOrderService = new BuyOrderService();
                if(isset($request->dashboard_type) && $request->dashboard_type == 'dashboard') {
                    $data['orders'] = $buyOrderService->getAllOrders($request->base_coin_id, $request->trade_coin_id)->limit($perPage)->get();
                } else {
                    $data['orders'] = $buyOrderService->getAllOrders($request->base_coin_id, $request->trade_coin_id);
                }
                $data['order_type'] = 'buy';
                $data['total_volume'] = $volume['total_buy_amount'];
                $response = [
                    'status' => true,
                    'message' => '',
                    'data' => $data
                ];
            } else {
                $sellOrderService = new SellOrderService();
                $buyOrderService = new BuyOrderService();

                if(isset($request->dashboard_type) && $request->dashboard_type == 'dashboard') {
                    $data['buy_orders'] = $buyOrderService->getAllOrders($request->base_coin_id, $request->trade_coin_id)->limit($perPage)->get();
                    $data['sell_orders'] = $sellOrderService->getAllOrders($request->base_coin_id, $request->trade_coin_id)->limit($perPage)->get();
                } else {
                    $data['buy_orders'] = $buyOrderService->getAllOrders($request->base_coin_id, $request->trade_coin_id)->paginate($perPage)->appends($request->all());
                    $data['sell_orders'] = $sellOrderService->getAllOrders($request->base_coin_id, $request->trade_coin_id)->paginate($perPage)->appends($request->all());
                }
                $data['order_type'] = 'buy_sell';
                $data['total_sell_volume'] = $volume['total_sell_amount'];
                $data['total_buy_volume'] = $volume['total_buy_amount'];
                $response = [
                    'status' => true,
                    'message' => '',
                    'data' => $data
                ];
            }
        } catch (\Exception $e) {
            Log::info('get all order exception -> '.$e->getMessage());
        }

        return $response;
    }

    // get my orders

    public function getMyOrders($request)
    {
        $response = [
            'status' => false,
            'message' => __('Something went wrong'),
            'data' => []
        ];
        try {
            $setting_per_page = isset(allsetting()['user_pagination_limit']) ? allsetting()['user_pagination_limit'] : 10;
            $perPage = empty($request->per_page) ? $setting_per_page : $request->per_page;

            if ($request->order_type == 'sell') {
                $sellOrderService = new SellOrderService();
                if(isset($request->dashboard_type) && $request->dashboard_type == 'dashboard') {
                    $data['orders'] = $sellOrderService->getMyOrders($request->base_coin_id, $request->trade_coin_id, getUserId())->limit(20)->get();
                } else {
                    $data['orders'] = $sellOrderService->getMyOrders($request->base_coin_id, $request->trade_coin_id, getUserId())->paginate($perPage)->appends($request->all());
                }
                $response = [
                    'status' => true,
                    'message' => '',
                    'data' => $data
                ];
            } else if ($request->order_type == 'buy') {
                $buyOrderService = new BuyOrderService();
                if(isset($request->dashboard_type) && $request->dashboard_type == 'dashboard') {
                    $data['orders'] = $buyOrderService->getMyOrders($request->base_coin_id, $request->trade_coin_id, getUserId())->limit(20)->get();
                } else {
                    $data['orders'] = $buyOrderService->getMyOrders($request->base_coin_id, $request->trade_coin_id, getUserId())->paginate($perPage)->appends($request->all());
                }
                $response = [
                    'status' => true,
                    'message' => '',
                    'data' => $data
                ];
            } else {
                $sellOrderService = new SellOrderService();
                $sellOrders = $sellOrderService->getMyOrders($request->base_coin_id, $request->trade_coin_id, getUserId())->get()->toArray();
                $buyOrderService = new BuyOrderService();
                $buyOrders = $buyOrderService->getMyOrders($request->base_coin_id, $request->trade_coin_id, getUserId())->get()->toArray();

                $data['orders'] = array_merge($buyOrders, $sellOrders);
                usort($data['orders'], function ($a, $b) {
                    return strtotime($b['created_at']) - strtotime($a['created_at']);
                });

                $response = [
                    'status' => true,
                    'message' => '',
                    'data' => $data
                ];
            }
        } catch (\Exception $e) {
            Log::info('get my order exception -> '.$e->getMessage());
        }

        return $response;
    }


    // get my transaction
    public function getMyTradeHistory($request)
    {
        $response = [
            'status' => false,
            'message' => __('Something went wrong'),
            'data' => []
        ];
        try {
            $setting_per_page = isset(allsetting()['user_pagination_limit']) ? allsetting()['user_pagination_limit'] : 10;
            $perPage = empty($request->per_page) ? $setting_per_page : $request->per_page;

            $transactionService = new TransactionService();
            if($request->per_page == 'all') {
                $data['transactions'] = $transactionService->getMyTradeHistory($request->base_coin_id, $request->trade_coin_id, getUserId(), $request->order_type ?? null, 0)->get();
            } else {
                if(isset($request->dashboard_type) && $request->dashboard_type == 'dashboard') {
                    $data['transactions'] = $transactionService->getMyTradeHistory($request->base_coin_id, $request->trade_coin_id, getUserId(), $request->order_type ?? null, $request->duration ?? null)->limit(20)->get();
                } else {
                    $data['transactions'] = $transactionService->getMyTradeHistory($request->base_coin_id, $request->trade_coin_id, getUserId(), $request->order_type ?? null, $request->duration ?? null)->paginate($perPage)->appends($request->all());
                }
            }
            $response = [
                'status' => true,
                'message' => __('Data get successfully'),
                'data' => $data
            ];

        } catch (\Exception $e) {
            Log::info('get my trade history exception -> '.$e->getMessage());
        }

        return $response;
    }

    public function getAllTransactionHistory($request)
    {
        $response = [
            'status' => false,
            'message' => __('Something went wrong'),
            'data' => []
        ];
        try {
            $perPage = empty($request->per_page) ? allsetting('user_pagination_limit') : $request->per_page;
            $transactionService = new TransactionService();
            if(isset($request->dashboard_type) && $request->dashboard_type == 'dashboard') {
                $data['transactions'] = $transactionService->getMyAllTransactionHistory(Auth::id())
                    ->limit(30)
                    ->get();
            } else {
                $data['transactions'] = $transactionService->getMyAllTransactionHistory(Auth::id())
                    ->paginate($perPage)
                    ->appends($request->all());
            }
            $response = [
                'status' => true,
                'message' => __('Data get successfully'),
                'data' => $data
            ];

        } catch (\Exception $e) {
            Log::info('get my all trade history exception -> '.$e->getMessage());
        }
        return response()->json($response);
    }

    public function getMarketTransactions($request)
    {
        $response = [
            'status' => false,
            'message' => __('Something went wrong'),
            'data' => []
        ];
        try {
            $setting_per_page = isset(allsetting()['user_pagination_limit']) ? allsetting()['user_pagination_limit'] : 50;
            $perPage = empty($request->per_page) ? $setting_per_page : $request->per_page;

            $transactionService = new TransactionService();
            if(isset($request->dashboard_type) && $request->dashboard_type == 'dashboard') {
                $data['transactions'] = $transactionService->getAllTradeHistory($request->base_coin_id, $request->trade_coin_id)->limit($perPage)->get();
            } else {
                $data['transactions'] = $transactionService->getAllTradeHistory($request->base_coin_id, $request->trade_coin_id)->paginate($perPage)->appends($request->all());
            }
            $response = [
                'status' => true,
                'message' => __('Data get successfully'),
                'data' => $data
            ];
        } catch (\Exception $e) {
            Log::info('get market trade history exception -> '.$e->getMessage());
        }

        return $response;
    }

    public function deleteOrder($id, $type)
    {
        DBService::beginTransaction();
        try {
            $service = null;
            if ($type == 'buy') {
                $service = new BuyOrderService();
            } elseif ($type == 'sell') {
                $service = new SellOrderService();
            } else {
                DBService::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => __('invalid order type')
                ]);
            }
            $order = $service->getDocs(['id' => $id, 'user_id' => Auth::id(), 'status' => 0])->first();
            if (empty($order)) {
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => __('no order found')
                ];
            }

            $restAmount = bcsub($order->amount, $order->processed);
            $walletRepository = new UserWalletRepository(Wallet::class);

            if ($type == 'buy') {
                $fees = $order->maker_fees > $order->taker_fees ? $order->maker_fees : $order->taker_fees;
                $total = bcmul($restAmount, $order->price);
                $returnAmount = bcadd($total, bcdiv(bcmul($total, $fees), "100"));
                $wallet = $walletRepository->getDocs(['user_id' => Auth::id(), 'coin_id' => $order->base_coin_id])->first();
                $response = $walletRepository->addBalanceById(Auth::id(), $order->base_coin_id, $returnAmount);
                $orderService = new BuyOrderRepository(Buy::class);
            } else {
                $wallet = $walletRepository->getDocs(['user_id' => Auth::id(), 'coin_id' => $order->trade_coin_id])->first();
                $response = $walletRepository->addBalanceById(Auth::id(), $order->trade_coin_id, $restAmount);
                $orderService = new SellOrderRepository(Sell::class);
            }

            if ($response == false) {
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => __('something went wrong')
                ];
            }
            $isDeleteOrUpdate = false;
            if ($order->processed > 0) {
                $isDeleteOrUpdate = $orderService->updateWhere(['id' => $order->id, 'user_id' => Auth::id(), 'status' => 0], ['status' => 1, 'amount' => $order->processed]);
            } else {
                $isDeleteOrUpdate = $orderService->deleteWhere(['id' => $order->id, 'user_id' => Auth::id(), 'processed' => 0, 'status' => 0, 'deleted_at' => null]);
            }
            if(!$isDeleteOrUpdate){
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => __('no order found')
                ];
            }
            DBService::commit();
            $order->amount = $order->processed;
            broadcastOrderData($order, $type, 'orderRemove');
            broadcastWalletData($wallet->id);

            return [
                'status' => true,
                'message' => __('order deleted successfully')
            ];
        } catch (\Exception $exception) {
            DBService::rollBack();
            return [
                'status' => false,
                'message' => __('something went wrong') . $exception->getMessage() . $exception->getLine()
            ];
        }
    }

    // get two market trade data
    public function getDashboardMarketTradeDataTwo($base_coin_id, $trade_coin_id,$limit)
    {
        $transactionService = new TransactionService();
        return $transactionService->getAllTradeHistory($base_coin_id, $trade_coin_id)->limit($limit)->orderBy('id','desc')->get();
    }

}
