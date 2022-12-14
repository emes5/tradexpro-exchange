<?php

namespace App\Listeners;

use App\Events\OrderHasPlaced;
use App\Http\Repositories\BuyOrderRepository;
use App\Http\Services\DashboardService;
use App\Http\Services\Logger;
use App\Http\Services\BuySellTransactionService;
use App\Http\Services\TradingViewChartService;
use App\Model\Buy;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StartProcessingOrder
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(OrderHasPlaced $event)
    {
        $logger = new Logger(env('TRADING_LOG'));

        $logger->log('Event start', "==============================");

        try{
            $type = Str::singular($event->order->getTable());
            $isMarket = ($type == 'buy' && $event->order->request_amount != 0) ? $event->order->is_market : 0;
            $logger->log('$isMarket', $isMarket);
            if($isMarket){
                $logger->log('Spacial Market Order', 'start');
                $service = new BuySellTransactionService();
                $repo = new BuyOrderRepository(Buy::class);
                $order = $repo->getDocs(['id' => $event->order->id, 'status' => 0])->first();
                $orderId = $order->id;
                $beingProcessingOrders = $service->_getBeingProcessingOrders($order, $type);
                $logger->log('$beingProcessingOrders', json_encode($beingProcessingOrders));
                if ($beingProcessingOrders->isEmpty()) {
//            $this->closeOrder($order, $orderType);
                    $message = __("No :orderType order found for this :type order.", ['orderType' => 'sell' , 'type' => $type]);
                    $logger->log('Order', $message);
                    return;
                }
                $loop = 0;
                foreach ($beingProcessingOrders as $beingProcessingOrder) {
                    $logger->log('$beingProcessingOrders loop ', $loop);
                    $order = $repo->getDocs(['id' => $orderId, 'status' => 0])->first();
                    $logger->log('$order->request_amount ', $order->request_amount);
                    $logger->log('$order->processed_request_amount ', $order->processed_request_amount);

                    if(!empty($order) && bccomp($order->request_amount , $order->processed_request_amount) == 1){
                        $logger->log('buy order', json_encode($order));
                        $logger->log('sell order', json_encode($beingProcessingOrder));
                        $price = $beingProcessingOrder->price;
                        $logger->log('$beingProcessingOrder->price ', $beingProcessingOrder->price);
                        if($service->refundIfFeesZero($beingProcessingOrder,'sell',$price) && $service->refundIfFeesZeroMarket($order,$price)){
                            $temporaryFees = calculated_fee_limit($order->user_id);
                            $logger->log('$temporaryFees ', $temporaryFees);

                            $logger->log('bcmul(bcsub($beingProcessingOrder->amount, $beingProcessingOrder->processed),$price) ', bcmul(bcsub($beingProcessingOrder->amount, $beingProcessingOrder->processed),$price));
                            $logger->log('bcsub($order->request_amount,$order->processed_request_amount) ', bcsub($order->request_amount,$order->processed_request_amount));

                            if(bcmul(bcsub($beingProcessingOrder->amount, $beingProcessingOrder->processed),$price) > bcsub($order->request_amount,$order->processed_request_amount)){
                                $amount = bcsub($order->request_amount,$order->processed_request_amount);
                                $logger->log('$amount 1 ', $amount);
                            }else{
                                $amount = bcsub($beingProcessingOrder->amount, $beingProcessingOrder->processed);
                                $logger->log('$amount 2 ', $amount);
                            }
                            $logger->log('$amount == ', $amount);
                            $input = [
                                'user_id' => $order->user_id,
                                'trade_coin_id' => $order->trade_coin_id,
                                'base_coin_id' => $order->base_coin_id,
                                'amount' => $amount,
                                'request_amount' => 0,
                                'processed' => 0,
                                'virtual_amount' => 0,
                                'price' => $price,
                                'btc_rate' => 0,
                                'is_market' => 1,
                                'category' => 1,
                                'maker_fees' => custom_number_format($temporaryFees['maker_fees']),
                                'taker_fees' => custom_number_format($temporaryFees['taker_fees']),
                                'is_conditioned' => 0,
                            ];

                            $order->increment('processed_request_amount',bcmul($price,$amount));
                            $logger->log('Old Order after update', json_encode($order));
                            $logger->log('Buy order placed', json_encode($input));
                            $success = Buy::create($input);
                            $logger->log('$success buy create == ', json_encode($success));
                            $logger->log('$beingProcessingOrder == ', 'end ------------ '.$loop);

                        }else{
                            continue;
                        }
                    }else{
                      break;
                    }
                    $loop = $loop+1;
                }
                $request = [];
                $request['base_coin_id'] = $order->base_coin_id;
                $request['trade_coin_id'] = $order->trade_coin_id;
                $request['dashboard_type'] = 'dashboard';
                $request['per_page'] = '';
                $time = time();
                $interval = 1440;
                $startTime = $time - 864000;
                $endTime = $time;
                $socket_data = [];
                $d_service = new DashboardService();
                $socket_data['trades'] = $d_service->getMarketTransactions((object) $request)['data'];
                $chartService = new TradingViewChartService();
                $socket_data['chart'] = $chartService->getChartData($startTime, $endTime, $interval, $order->base_coin_id, $order->trade_coin_id);
                $channel_name = 'trade-info-'.$order->base_coin_id.'-'.$order->trade_coin_id;
                $event_name = 'process';
                $socket_data['summary'] = $d_service->getOrderData((object) $request)['data'];
                $socket_data['update_trade_history'] = false;
                sendDataThroughWebSocket($channel_name,$event_name,$socket_data);
                $socket_data = $d_service->getMyTradeHistory((object) $request)['data'];
                $channel_name = 'trade-history-'.$order->base_coin_id.'-'.$order->trade_coin_id.'-'.$order->user_id;
                sendDataThroughWebSocket($channel_name,$event_name,$socket_data);
            }else{
                $logger->log('Normal Order', 'transaction processing');
                app(BuySellTransactionService::class)->process($event->order->id, $type);

            }
        }catch (\Exception $e){
            $logger->log('Event Error', $e->getMessage().'file'.$e->getFile().' line'.$e->getLine());
        }

        $logger->log('Event End', "==============================");

    }
}
