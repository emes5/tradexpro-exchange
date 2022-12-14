<?php

namespace App\Http\Services;


use App\Http\Repositories\WalletRepository;
use App\Model\AdminSendCoinHistory;
use App\Model\Coin;
use App\Model\DepositeTransaction;
use App\Model\Wallet;
use App\Model\WalletAddressHistory;
use App\Model\WalletNetwork;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public $repository;
    public $logger;
    public $bitgoService;

    public function __construct()
    {
        $this->logger =new Logger();
        $this->repository = new WalletRepository();
        $this->bitgoService = new BitgoWalletService();
    }

    // user wallet list
    public function userWalletList($userId,$request)
    {
        try {
            if(isset($request->type) && $request->type == 'usd') {
                $list = $this->repository->getMyWalletListWithOnorderWithTotal($userId,$request->per_page);
            } else {
                storeException('userWalletList','called');
                create_coin_wallet(Auth::id());
                $list = $this->repository->getMyWalletListWithOnorderWithTotalWithoutUSD($userId,$request->per_page);
            }

            $data['wallets'] = $list['wallets'];
            $data['total'] = $list['total'];
            $data['currency'] = 'USD';
            $response = ['success' => true, 'message' => __('Data get'), 'data' => $data];
        } catch (\Exception $e) {
            $this->logger->log('userWalletList', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        }
        return $response;
    }

    //get user wallet list only
    public function getUserWalletList($userId)
    {
        return $this->repository->getUserWalletList($userId);
    }

    // user wallet deposit address
    public function userWalletDeposit($userId,$walletId)
    {
        try {
            $response = $this->repository->walletDeposit($userId,$walletId);
        } catch (\Exception $e) {
            $this->logger->log('userWalletList', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        }
        return $response;
    }

    // user wallet withdrawal
    public function userWalletWithdrawal($userId,$walletId)
    {
        try {
            $response = $this->repository->walletWithdrawal($userId,$walletId);
        } catch (\Exception $e) {
            $this->logger->log('userWalletWithdrawal', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        }
        return $response;
    }

    public function get_wallet_rate($request){
        return $this->repository->get_wallet_rate($request);
    }

    public function coinSwap($from_wallet, $to_wallet, $converted_amount, $requested_amount, $rate){
        return $this->repository->coinSwap($from_wallet, $to_wallet, $converted_amount, $requested_amount, $rate);
    }

    // bitgo wallet deposit
    public function bitgoWalletCoinDeposit($coinType, $walletId, $txId)
    {
        try {
            $bitgoService = new BitgoWalletService();
            $checkHash = DepositeTransaction::where(['transaction_id' => $txId])->first();
            if (isset($checkHash)) {
                $this->logger->log('bitgoWalletCoinDeposit hash already in db ', $txId);
            } else {
                $getTransaction = $this->getTransaction($coinType, $walletId, $txId);
                if ($getTransaction['success'] == true) {
                    $transactionData = $getTransaction['data'];
                    if ($transactionData['type'] == 'receive') {
                        $coinVal = $bitgoService->getDepositDivisibilityValues($transactionData['coin']);
                        $amount = bcdiv($transactionData['value'],$coinVal,8);

                        $data = [
                            'coin_type' => $transactionData['coin'],
                            'txId' => $transactionData['txid'],
                            'confirmations' => $transactionData['confirmations'],
                            'amount' => $amount
                        ];

                        if (isset($transactionData['entries'][0])) {
                            foreach ($transactionData['entries'] as $entry) {
                                if (isset($entry['wallet']) && ($entry['wallet'] == $transactionData['wallet'])) {
                                    $data['address'] = $entry['address'];
                                    $this->logger->log('entry address', $data['address']);
                                }
                            }
                        }

                        if(isset($data['address'])) {
                            $this->checkAddressAndDeposit($data);
                        }
                    } else {
                        $this->logger->log('bitgoWalletCoinDeposit type', 'the transaction type is not receive');
                    }
                } else {
                    $this->logger->log('bitgoWalletCoinDeposit failed', $getTransaction['message']);
                }
            }

        } catch (\Exception $e) {
            $this->logger->log('bitgoWalletCoinDeposit', $e->getMessage());
        }
    }

    // get transaction
    public function getTransaction($coinType, $walletId, $txId)
    {
        try {
            $bitgoResponse = $this->bitgoService->transferBitgoData($coinType,$walletId,$txId);
            $this->logger->log('getTransaction response ', json_encode($bitgoResponse));
            if ($bitgoResponse['success']) {

                $response = [
                    'success' => true,
                    'message' => __('Data get successfully'),
                    'data' => $bitgoResponse['data']
                ];
            } else {
                $this->logger->log('getTransaction', $bitgoResponse['message']);
                $response = [
                    'success' => false,
                    'message' => $bitgoResponse['message'],
                    'data' => []
                ];
            }
        } catch (\Exception $e) {
            $this->logger->log('bitgoWalletWebhook getTransaction', $e->getMessage());
            $response = [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
        return $response;
    }
// check deposit address
    public function checkAddressAndDeposit($data)
    {
        try {
            $this->logger->log('checkAddressAndDeposit', json_encode($data));
            $checkAddress = WalletAddressHistory::where(['address' => $data['address'], 'coin_type' => $data['coin_type']])->first();
            if ($checkAddress) {
                $wallet = Wallet::find($checkAddress->wallet_id);
                if ($wallet) {
                    $this->logger->log('checkAddressAndDeposit wallet ', json_encode($wallet));
                    $deposit = DepositeTransaction::create($this->depositData($data,$wallet));
                    $this->logger->log('checkAddressAndDeposit created ', json_encode($deposit));
                    $this->logger->log('checkAddressAndDeposit wallet balance before ', $wallet->balance);
                    $wallet->increment('balance',$data['amount']);
                    $this->logger->log('checkAddressAndDeposit wallet balance increment ', $wallet->balance);
                    $this->logger->log('checkAddressAndDeposit', ' wallet deposit successful');
                } else {
                    $this->logger->log('checkAddressAndDeposit', ' wallet not found');
                }
            } else {
                $this->logger->log('checkAddressAndDeposit', $data['address'].' this address not found in db ');
            }
        } catch (\Exception $e) {
            $this->logger->log('checkAddressAndDeposit', $e->getMessage());
        }
    }
// deposit data
    public function depositData($data,$wallet)
    {
        return [
            'address' => $data['address'],
            'receiver_wallet_id' => $wallet->id,
            'address_type' => ADDRESS_TYPE_EXTERNAL,
            'coin_type' => $wallet->coin_type,
            'amount' => $data['amount'],
            'transaction_id' => $data['txId'],
            'status' => STATUS_SUCCESS,
            'confirmations' => $data['confirmations']
        ];
    }

    // send coin balance to user
    public function sendCoinBalanceToUser($request)
    {
        try {
            if(isset($request->wallet_id[0])) {
                $wallets = $request->wallet_id;
                $counts = sizeof($request->wallet_id);
                for($i = 0; $i < $counts; $i++) {
                    $wallet = Wallet::find($wallets[$i]);
                    if (isset($wallet)) {
                        AdminSendCoinHistory::create($this->balanceSendData($wallet,$request->amount,Auth::id()));
                        $wallet->increment('balance',$request->amount);
                    }
                }
                $response = responseData(true,__('Coin sent successful'));
            } else {
                $response = responseData(false,__('Must select at least one wallet'));
            }
        } catch (\Exception $e) {
            storeException('sendCoinBalanceToUser',$e->getMessage());
            $response = responseData(false,__('Something went wrong'));
        }
        return $response;
    }

    // make wallet send history data
    public function balanceSendData($wallet,$amount,$authId)
    {
        return [
            'user_id' => $wallet->user_id,
            'wallet_id' => $wallet->id,
            'amount' => $amount,
            'updated_by' => $authId
        ];
    }

    // generate address
    public function getWalletNetworkAddress($request,$userId)
    {
        try {
            $wallet = Wallet::where(['id' => $request->wallet_id, 'user_id' => $userId])->first();
            if ($wallet) {
                if ($wallet->coin_type == 'USDT') {
                    $networkAddress = WalletNetwork::where(['wallet_id' => $wallet->id, 'network_type' => $request->network_type])->first();
                    if (empty($networkAddress)) {
                        $networkAddress = WalletNetwork::firstOrCreate(['wallet_id' => $wallet->id, 'network_type' => $request->network_type],['coin_id' => $wallet->coin_id]);
                    }
                    if (empty($networkAddress->address)) {
                        $address = get_coin_address($networkAddress->network_type,$wallet->coin->network);
                        if (!empty($address['address'])) {
                            $networkAddress->update(['address' => $address['address']]);
                            $networkAddress = WalletNetwork::where(['wallet_id' => $wallet->id, 'network_type' => $request->network_type])->first();
                        }
                    }
                    if (empty($networkAddress->address)) {
                        $response = responseData(false,__('Address generate failed'),$networkAddress);
                    } else {
                        $response = responseData(true,__('Address generated successfully'),$networkAddress);
                    }
                } else {
                    $response = responseData(false,__('No need to create address with this coin'));
                }
            } else {
                $response = responseData(false,__('Wallet not found'));
            }
        } catch (\Exception $e) {
            storeException('getWalletNetworkAddress',$e->getMessage());
            $response = responseData(false);
        }
        return $response;
    }
}
