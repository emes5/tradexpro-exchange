<?php

namespace App\Http\Services;

use App\Http\Repositories\AffiliateRepository;
use App\Jobs\DistributeWithdrawalReferralBonus;
use App\Jobs\MailSend;
use App\Jobs\Withdrawal;
use App\Model\Coin;
use App\Model\CoWalletWithdrawApproval;
use App\Model\DepositeTransaction;
use App\Model\TempWithdraw;
use App\Model\Wallet;
use App\Model\WalletAddressHistory;
use App\Model\WalletCoUser;
use App\Model\WalletNetwork;
use App\Model\WithdrawHistory;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TransService
{
    protected $logger;

    public function __construct()
    {
        $this->logger = new Logger();
    }

    private function generate_email_verification_key()
    {
        do {
            $key = Str::random(60);
        } While (User::where('email_verified', $key)->count() > 0);

        return $key;
    }

    // make withdrawal data
    private function make_withdrawal_data($data,$address_type,$fees,$trans_id,$receiverWallet = null)
    {
       return [
            'wallet_id' => $data['wallet']->id,
            'address' => $data['address'],
            'amount' => $data['amount'],
            'address_type' => $address_type,
            'fees' => $fees,
            'coin_type' => $data['wallet']->coin_type,
            'transaction_hash' => $trans_id,
            'confirmations' => 0,
            'status' => STATUS_PENDING,
            'message' => $data['note'],
            'receiver_wallet_id' => is_null($receiverWallet) ? 0 : $receiverWallet->id,
            'user_id' => $data['user']->id,
           'network_type' => $data['network_type'] ?? ''
        ];
    }
    // withdrawal process from job
    public function send($data)
    {
        try {

            $wallet = Wallet::join('coins', 'coins.id', '=', 'wallets.coin_id')
                ->where(['wallets.id'=>$data['wallet_id'], 'wallets.user_id'=> $data['user']->id])
                ->select('wallets.*', 'coins.name as coin_name', 'coins.status as coin_status', 'coins.status as coin_status', 'coins.is_withdrawal', 'coins.minimum_withdrawal',
                    'coins.maximum_withdrawal', 'coins.withdrawal_fees', 'coins.max_send_limit')
                ->first();
            $user = $wallet->user;
            $mail_info = [];
            $coin_name = strtolower($wallet->coin_name);
            $mail_info['mailTemplate'] = 'email.transaction_mail';
//            $sender_wallet_address = WalletAddressHistory::where('wallet_id',$wallet->id)->first()->address;
            if ($wallet) {
                if ($wallet->coin_type == COIN_USDT) {
                    $checkNetwork = WalletNetwork::where(['wallet_id' => $wallet->id, 'network_type' => $data['network_type']])->first();
                    if(empty($checkNetwork)) {
                        storeException('withdrawal usdt network not found',$data['network_type']);
                        return responseData(false,__('Selected network not found'));
                    }
                }
                $checkValidate = $this->checkWithdrawalValidation( $data['address'], $data['amount'], $data['user'], $wallet);
                if ($checkValidate['success'] ==  false) {
                    $response = [
                        'success' => false,
                        'message' => $checkValidate['message'],
                        'data' => ''
                    ];
                    $this->logger->log('send job', json_encode($response));
                    return $response;
                }
                $checkKyc = $this->kycValidationCheck($data['user']->id);
                if ($checkKyc['success'] == false) {
                    $response = [
                        'success' => false,
                        'message' => $checkKyc['message'],
                        'data' => ''
                    ];
                    $this->logger->log('send job', json_encode($response));
                    return $response;
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => __('Wallet not found'),
                    'data' => ''
                ];
                $this->logger->log('send job', json_encode($response));
                return $response;
            }
            $trans_id = Str::random(32);// we make this same for deposit and withdrawl
            DB::beginTransaction();
            try {
                $walletAddress = $this->isInternalAddress($data['address']);
                if ( empty($walletAddress) ) {
                    $receiverWallet = null;
                    $receiverUser = null;
                    $address_type = ADDRESS_TYPE_EXTERNAL;
                    $fees = $checkValidate['data']['fees'];
                } else {
                    $fees = $checkValidate['data']['fees'];
                    $receiverWallet = $walletAddress->wallet;
                    $receiverUser = $walletAddress->wallet->user;
                    $address_type = ADDRESS_TYPE_INTERNAL;
                    if ( $user->id == $receiverUser->id ) {
                        Log::info('You can not send to your own wallet!');
                        return ['success' => false, 'message' => __('You can not send to your own wallet!')];
                    }
                    if ($wallet->coin_type != $walletAddress->wallet->coin_type) {
                        Log::info('You can not make withdrawal, because wallet coin type is mismatched. Your wallet coin type and withdrawal address coin type should be same.');
                        return ['success' => false, 'message' => __('You can not make withdrawal, because wallet coin type is mismatched. Your wallet coin type and withdrawal address coin type should be same.')];
                    }
                }
                if ( ($data['amount'] + $fees) > $wallet->balance) {
                    Log::info('Insufficient Balance!');
                    return ['success' => false, 'message' => 'Insufficient Balance!'];
                }
                $sendAmount = $data['amount'] + $fees;
                $wallet->decrement('balance', $sendAmount);
                $data['wallet'] = $wallet;

                $transaction = WithdrawHistory::create($this->make_withdrawal_data($data,$address_type,$fees,$trans_id,$receiverWallet));
                $this->logger->log('send job withdrawal data', json_encode($transaction));
                $mail_info['to'] = $user->email;
                $mail_info['name'] = $user->first_name.' '.$user->last_name;
                if($address_type == ADDRESS_TYPE_INTERNAL){
                    $mail_info_address_type = 'Internal';
                }else{
                    $mail_info_address_type = 'External';
                }
                if ($address_type == ADDRESS_TYPE_INTERNAL) {
                    $pendingAmount = WithdrawHistory::where(['wallet_id' => $wallet->id, 'status' => STATUS_PENDING])
                        ->where('created_at', '>=', Carbon::now()->subDay())
                        ->sum('amount');
                    if ($pendingAmount < $wallet->max_send_limit) {
                        $transaction->status = STATUS_SUCCESS;
                        $transaction->save();
                        $mail_info['subject'] = "TransactionID:<$trans_id> Withdrawal ($data[amount] $coin_name) placed successfully.";
                        $withdraw_status = 'Successful';
                    } else{
                        $mail_info['subject'] = "TransactionID:<$trans_id> Withdrawal ($data[amount] $coin_name) placed successfully. Waiting for admin approval!";
                        $withdraw_status = 'Waiting for admin approval';
                    }
                    $mail_info['email_message']="$data[amount] $coin_name Withdrawal placed successfully from $wallet->name. Transaction Information given below:";
                    $mail_info['email_message_table'] = "<table>
                        <tbody>
                            <tr>
                                <td>Sender Wallet</td>
                                <td>$wallet->coin_type</td>
                            </tr>
                            <tr>
                                <td>Receiver Address</td>
                                <td>$data[address]</td>
                            </tr>
                            <tr>
                                <td>Address Type</td>
                                <td>$mail_info_address_type</td>
                            </tr>
                            <tr>
                                <td>TransactionID</td>
                                <td>$trans_id</td>
                            </tr>
                            <tr>
                                <td>Amount</td>
                                <td>$data[amount] $coin_name</td>
                            </tr>
                            <tr>
                                <td>Status</td>
                                <td>$withdraw_status</td>
                            </tr>
                        </tbody>
                    </table>";
                    dispatch(new MailSend($mail_info))->onQueue('send-mail-withdrawal');
                    if ( !empty($receiverWallet) ) {
                        $mail_info['to'] = $receiverUser->email;
                        $mail_info['name'] = $receiverUser->first_name.' '.$receiverUser->last_name;
                        $mail_info['email_message']="$data[amount] $coin_name deposit placed successfully from $receiverWallet->name. Transaction Information given below:";
                        $receive_tr =  DepositeTransaction::create($this->makeDepositData($data,$address_type,$fees,$trans_id,$receiverWallet->id));
                        Log::info(json_encode($receive_tr));
                        if ($pendingAmount < $wallet->max_send_limit) {
                            $receive_tr->status = STATUS_SUCCESS;
                            $receive_tr->save();
                            $receiverWallet->increment('balance', $data['amount']);
                            $mail_info['subject'] = "TransactionID:<$trans_id> Deposit ($data[amount] $coin_name) placed successfully.";
                            dispatch(new MailSend($mail_info))->onQueue('send-mail-deposit');
                        }else{
                            $mail_info['subject'] = "TransactionID:<$trans_id> Deposit ($data[amount] $coin_name) placed successfully. Waiting for admin approval!";
                            dispatch(new MailSend($mail_info))->onQueue('send-mail-deposit');
                        }
                    }
                } else {
                    $mail_info['subject'] = "TransactionID:<$trans_id> Withdrawal ($data[amount] $coin_name) placed successfully. Waiting for admin approval!";
                    $withdraw_status = 'Waiting for admin approval';
                    $mail_info['email_message']="$data[amount] $coin_name Withdrawal placed successfully from $wallet->name. Transaction Information given below:";
                    $mail_info['email_message_table'] = "<table>
                        <tbody>
                            <tr>
                                <td>Sender Address</td>
                                <td>$wallet->coin_type</td>
                            </tr>
                            <tr>
                                <td>Receiver Address</td>
                                <td>$data[address]</td>
                            </tr>
                            <tr>
                                <td>Address Type</td>
                                <td>$mail_info_address_type</td>
                            </tr>
                            <tr>
                                <td>TransactionID</td>
                                <td>$trans_id</td>
                            </tr>
                            <tr>
                                <td>Amount</td>
                                <td>$data[amount] $coin_name</td>
                            </tr>
                            <tr>
                                <td>Status</td>
                                <td>$withdraw_status</td>
                            </tr>
                        </tbody>
                    </table>";
                    dispatch(new MailSend($mail_info))->onQueue('send-mail-withdrawal');
                }
            } catch (\Exception $e) {
                DB::rollBack();
                Log::info($e->getMessage());
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        } catch (\Exception $e) {
            Log::info('coin send exception '.$e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
        DB::commit();
        return [
            'success' => true,
            'message' => __('Transaction successful.')
        ];
    }

    //make deposit data
    public function makeDepositData($data,$address_type,$fees,$trans_id,$receiverWalletId)
    {
        return [
            'address' => $data['address'],
            'address_type' => $address_type,
            'amount' => $data['amount'],
            'fees' => $fees,
            'coin_type' => $data['wallet']->coin_type,
            'transaction_id' => $trans_id,
            'confirmations' => 0,
            'status' => STATUS_PENDING,
            'sender_wallet_id' => $data['wallet']->id,
            'receiver_wallet_id' => $receiverWalletId,
            'network_type' => $data['network_type'] ?? ''
        ];
    }
    // check internal address
    private function isInternalAddress($address)
    {
        return WalletAddressHistory::where('address', $address)->with('wallet')->first();
    }

    // cancel transaction
    private function _cancelTransaction($user, $wallet, $address, $amount, $pendingTransaction)
    {
        if ( !empty($pendingTransaction) ) {
            $pendingTransaction->status = STATUS_REJECTED;
            $pendingTransaction->update();
        }
        //  $mailService = app(MailService::class);
        $userName = $user->first_name . ' ' . $user->last_name;
        $userEmail = $user->email;
        $companyName = isset($default['company']) && !empty($default['company']) ? $default['company'] : __('Coin Wallet');
        $subject = __(':emailSubject | :companyName', ['emailSubject' => __('Send coin failure'), 'companyName' => $companyName]);
        $data['user'] = $user;
        $data['amount'] = $amount;
        $data['address'] = $address;
        $data['wallet'] = $wallet;
        //  $mailService->send('email.send_coin_failure', $data, $userEmail, $userName, $subject);
    }

    // external transfer
    public function external_transfer($address, $amount, $authId, $isAdmin, $user_id)
    {

        $coinPayment = new CoinPaymentsAPI();

        $api = $coinPayment->CreateWithdrawal($amount,'LTCT',$address);

        if ( isset($api->error) && ($api->error == 'ok') ) {
            return [
                'status' => true,
                'message' => __('Transfer successfully!'),
                'transaction_id' => $api->result['id']
            ];
        }
        return [
            'status' => false,
            'message' => __('Failed to send coin!'),
            'nodeMessage' => $api
        ];
    }





    private function calculate_fees($amount)
    {
        return $amount;
    }


    private function sendTransactionMail($sender_user, $mailTemplet, $receiver_user, $amount, $emailSubject)
    {
        $mailService = app(MailService::class);
        $userName = $sender_user->first_name . ' ' . $sender_user->last_name;
        $userEmail = $sender_user->email;
        $companyName = isset($default['company']) && !empty($default['company']) ? $default['company'] : __('Coin Wallet');
        $subject = __(':emailSubject | :companyName', ['emailSubject' => $emailSubject, 'companyName' => $companyName]);
        $data['data'] = $sender_user;
        $data['anotherUser'] = $receiver_user;
        $data['amount'] = $amount;
        $mailService->send($mailTemplet, $data, $userEmail, $userName, $subject);
    }

    private function sendExternalTransactionMail($sender_user, $mailTemplet, $address, $amount, $emailSubject)
    {
        $mailService = app(MailService::class);
        $userName = $sender_user->first_name . ' ' . $sender_user->last_name;
        $userEmail = $sender_user->email;
        $companyName = isset($default['company']) && !empty($default['company']) ? $default['company'] : __('Coin Wallet');
        $subject = __(':emailSubject | :companyName', ['emailSubject' => $emailSubject, 'companyName' => $companyName]);
        $data['data'] = $sender_user;
        $data['address'] = $address;
        $data['amount'] = $amount;
        $mailService->send($mailTemplet, $data, $userEmail, $userName, $subject);
    }

    private function sendVerificationSms($phone, $randno)
    {
        $smsText = 'Your ' . allsetting()['app_title'] . ' verification code is here ' . $randno;
        app(SmsService::class)->send($phone, $smsText);
    }



    // user deposit history
    public function depositTransactionHistories($user_id = null, $status = null, $wallet_id = null, $address_type = null, $transaction_id = null, $order_data=null)
    {
        $histories = DepositeTransaction::join('wallets', 'wallets.id', 'deposite_transactions.receiver_wallet_id')
            ->select('wallets.*','deposite_transactions.*');
        if ( !empty($status) )
            $histories = $histories->where('deposite_transactions.status', $status);
        if ( !empty($wallet_id) )
            $histories = $histories->where('wallets.id', $wallet_id);
        if ( !empty($address_type) )
            $histories = $histories->where('deposite_transactions.address_type', $address_type);
        if ( !empty($transaction_id) )
            $histories = $histories->where('deposite_transactions.transaction_id', $transaction_id);
        if ( !empty($user_id) )
            $histories = $histories->where('wallets.user_id', $user_id);
        if(!empty($order_data['column_name']) && !empty($order_data['order_by'])){
            $withdraw_columns = ['created_at','address','amount','fees','coin_type'];
            if(in_array($order_data['column_name'],$withdraw_columns)){
                $histories->orderBy("deposite_transactions.$order_data[column_name]", $order_data['order_by']);
            }else{
                $histories->orderBy("wallets.$order_data[column_name]", $order_data['order_by']);
            }
        }
        return $histories;
    }

    // user withdrawal history
    public function withdrawTransactionHistories($user_id = null, $status = null, $wallet_id = null, $address_type = null, $transaction_id = null, $order_data=null)
    {
        $histories = WithdrawHistory::join('wallets', 'wallets.id', 'withdraw_histories.wallet_id')
            ->select('wallets.*','withdraw_histories.*');
        if ( !empty($status) )
            $histories = $histories->where('withdraw_histories.status', $status);
        if ( !empty($wallet_id) )
            $histories = $histories->where('wallets.id', $wallet_id);
        if ( !empty($address_type) )
            $histories = $histories->where('withdraw_histories.address_type', $address_type);
        if ( !empty($transaction_id) )
            $histories = $histories->where('withdraw_histories.transaction_hash', $transaction_id);
        if ( !empty($user_id) )
            $histories = $histories->where('wallets.user_id', $user_id);
        if(!empty($order_data['column_name']) && !empty($order_data['order_by'])){
            $withdraw_columns = ['created_at','address','amount','fees','coin_type'];
            if(in_array($order_data['column_name'],$withdraw_columns)){
                $histories->orderBy("withdraw_histories.$order_data[column_name]", $order_data['order_by']);
            }else{
                $histories->orderBy("wallets.$order_data[column_name]", $order_data['order_by']);
            }
        }
        return $histories;
    }

    public function isAllApprovalDoneForCoWalletWithdraw($tempWithdraw) {
        if(empty($tempWithdraw)) {
            Log::warning('Empty temp withdrawal.');
            return ['success' => false, 'message' => __('Invalid withdrawal.')];
        }
        $response = $this->approvalCounts($tempWithdraw);
        if($response['alreadyApprovedUserCount'] >= $response['requiredUserApprovalCount']) {
            $tempWithdraw->status = STATUS_ACCEPTED;
            try {
                if(!$tempWithdraw->save()) throw new \Exception(__('Temp withdraw status success save failed'));
                return ['success' => true, 'message' => ''];
            } catch (\Exception $e) {
                Log::warning($e->getMessage());
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }
        else return ['success' => false, 'message' => __('Not enough approval done yet.')];
    }

    public function approvalCounts($tempWithdraw) {
        $userPercentageForApproval = settings(CO_WALLET_WITHDRAWAL_USER_APPROVAL_PERCENTAGE_SLUG);
        $userPercentageForApproval = !empty($userPercentageForApproval) ? $userPercentageForApproval : 60;
        $coUserCount = WalletCoUser::where(['wallet_id'=> $tempWithdraw->wallet_id])->count();
        $requiredUserApprovalCount = ceil($coUserCount * ($userPercentageForApproval / 100.0));
        $alreadyApprovedUserCount = CoWalletWithdrawApproval::where(['temp_withdraw_id'=> $tempWithdraw->id])->count();
        return ['requiredUserApprovalCount' => $requiredUserApprovalCount, 'alreadyApprovedUserCount' => $alreadyApprovedUserCount];
    }

    // check withdrawal validation
    public function checkWithdrawalValidation($address,$amount, $user, $wallet)
    {
        $fees = check_withdrawal_fees($amount, $wallet->withdrawal_fees);
        $data = [
            'data' => [
                'fees' => $fees,
                'amount' => $amount,
                'fees_percentage' => $wallet->withdrawal_fees
            ],
            'success' => true,
            'message' => __('Success')
        ];
        $walletAddress = $this->isInternalAddress($address);
        if ($walletAddress) {
            if ($walletAddress->wallet->user_id == $wallet->user_id) {
                $data = [
                    'data' => [],
                    'success' => false,
                    'message' => __('You can not send to your own wallet!')
                ];
                return $data;
            }
            if ($walletAddress->coin_type != $wallet->coin_type) {
                $data = [
                    'data' => [],
                    'success' => false,
                    'message' => __('Both wallet coin type should be same')
                ];
                return $data;
            }
        }
        $checkStatus = check_coin_status($wallet,$user->id,$amount,$fees);
        if ($checkStatus['success'] == false) {
            return $checkStatus;
        }
        return $data;
    }

    // admin pending withdrawal accept process

    public function acceptPendingExternalWithdrawal($transaction)
    {
        try {
            $coin = $transaction->coin;
            $currency =  !empty($transaction->network_type) ? $transaction->network_type : $transaction->coin_type;

            if ($coin->network == COIN_PAYMENT) {
                Log::info('acceptPendingExternalWithdrawal : coin payment');

                $coinPayment = new CoinPaymentsAPI();
                $response = $coinPayment->CreateWithdrawal($transaction->amount,$currency,$transaction->address);

                if (is_array($response) && isset($response['error']) && ($response['error'] == 'ok') ) {
                    $transaction->transaction_hash = $response['result']['id'];
                    $transaction->status = STATUS_SUCCESS;
                    $transaction->update();
                    dispatch(new DistributeWithdrawalReferralBonus($transaction))->onQueue('referral');

                    return ['success' => true, 'message' => __('Pending withdrawal accepted Successfully.')];
                } else {
                    return ['success' => false, 'message' => $response['error']];
                }
            } elseif ($coin->network == BITCOIN_API) {
                Log::info('acceptPendingExternalWithdrawal : coin payment');
                $result = $this->external_transfer_using_coin_api($currency,$transaction->address, $transaction->amount, Auth::id(), true, $transaction->user_id);

                if ($result['success'] == true) {
                    $transaction->transaction_hash = $result['transaction_id'];
                    $transaction->status = STATUS_SUCCESS;
                    $transaction->update();
                    dispatch(new DistributeWithdrawalReferralBonus($transaction))->onQueue('referral');

                    return ['success' => true, 'message' => __('Pending withdrawal accepted Successfully.')];
                } else {
                    return ['success' => false, 'message' => $result['message']];
                }
            } elseif ($coin->network == BITGO_API) {
                $result = $this->sendCoinWithBitgo($transaction);
                if ($result['success'] == true) {
                    $transaction->transaction_hash = $result['data'];
                    $transaction->status = STATUS_SUCCESS;
                    $transaction->update();
                    dispatch(new DistributeWithdrawalReferralBonus($transaction))->onQueue('referral');

                    return ['success' => true, 'message' => __('Pending withdrawal accepted Successfully.')];
                } else {
                    return ['success' => false, 'message' => $result['message']];
                }
            } elseif ($coin->network == ERC20_TOKEN || $coin->network == BEP20_TOKEN) {
                $result = $this->sendCoinWithERC20($transaction);
                if ($result['success'] == true) {
                    $transaction->transaction_hash = $result['data']['transaction_id'];
                    $transaction->used_gas = $result['data']['used_gas'];
                    $transaction->status = STATUS_SUCCESS;
                    $transaction->update();
                    dispatch(new DistributeWithdrawalReferralBonus($transaction))->onQueue('referral');

                    return ['success' => true, 'message' => __('Pending withdrawal accepted Successfully.')];
                } else {
                    return ['success' => false, 'message' => $result['message']];
                }
            } else {
                return ['success' => false, 'message' => __('No Api found')];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => __('Something went wrong')];
        }
    }


    // external transfer by using bit coin api
    public function external_transfer_using_coin_api($coinType,$address, $amount, $authId, $isAdmin, $user_id)
    {
        try {
            $coin = Coin::join('coin_settings','coin_settings.coin_id', '=', 'coins.id')
                ->where(['coins.coin_type' => $coinType])
                ->select('coins.*', 'coin_settings.*')
                ->first();
            if ($coin) {
                $api =  new BitCoinApiService($coin->coin_api_user,decryptId($coin->coin_api_pass),$coin->coin_api_host,$coin->coin_api_port);
                $response = $api->verifyAddress($address);
                if (!$response) {
                    return ['success' => false, 'message' => __('Not a valid address!')];
                }

                $adminId = null;
                $userId = $user_id;
                if ($isAdmin) {
                    $adminId = $authId;
                } else {
                    $userId = $authId;
                }

                $transaction_id = $api->sendToAddress($address, $amount, $userId, $adminId);
                log::info($transaction_id);
                if ($transaction_id) {
                    return [
                        'success' => true,
                        'message' => __('Transfer successfully!'),
                        'transaction_id' => $transaction_id
                    ];
                }
                return [
                    'success' => false,
                    'message' => __('Failed to send coin!'),
                    'nodeMessage' => $api
                ];
            } else {
                return [
                    'success' => false,
                    'message' => __('Failed to send coin,  coin not found'),
                    'nodeMessage' => ''
                ];
            }
        } catch (\Exception $e) {
            Log::info('external_transfer_using_coin_api '. $e->getMessage());
            return [
                'success' => false,
                'message' => __('Failed to send coin!'),
            ];
        }

    }

    // withdrawal process
    public function withdrawalProcess($request)
    {
        try {
            $google2faService = new User2FAService();
            $user = Auth::user();
            $wallet = Wallet::join('coins', 'coins.id', '=', 'wallets.coin_id')
                ->where(['wallets.id'=>$request->wallet_id, 'wallets.user_id'=> $user->id])
                ->select('wallets.*', 'coins.status as coin_status', 'coins.is_withdrawal', 'coins.minimum_withdrawal',
                    'coins.maximum_withdrawal', 'coins.withdrawal_fees', 'coins.max_send_limit')
                ->first();
            if ($wallet) {
                if ($wallet->coin_type == COIN_USDT) {
                    $checkNetwork = WalletNetwork::where(['wallet_id' => $wallet->id, 'network_type' => $request->network_type])->first();
                    if(empty($checkNetwork)) {
                        return responseData(false,__('Selected network not found'));
                    }
                }
                $checkValidate = $this->checkWithdrawalValidation($request->address, $request->amount, $user, $wallet);
                if ($checkValidate['success'] ==  false) {
                    $response = [
                        'success' => false,
                        'message' => $checkValidate['message'],
                        'data' => ''
                    ];
                } else {
                    $checkKyc = $this->kycValidationCheck($user->id);
                    if ($checkKyc['success'] == false) {
                        $response = [
                            'success' => false,
                            'message' => $checkKyc['message'],
                            'data' => ''
                        ];
                    } else {
                        $valid = $google2faService->userGoogle2faValidation($user,$request);
                        if ($valid['success']) {
                            $data = [
                                'wallet_id' => $wallet->id,
                                'amount' => $request->amount,
                                'address' => $request->address,
                                'note' => $request->note ?? '',
                                'user' => $user
                            ];
                            if ($wallet->coin_type == COIN_USDT) {
                                $data['network_type'] = $request->network_type;
                            }
                            dispatch(new Withdrawal($data))->onQueue('withdrawal');
                            $response = [
                                'success' => true,
                                'message' => __('Withdrawal process started successfully. We will notify you the result soon'),
                                'data' => ''
                            ];
                        } else {
                            $response = responseData(false,$valid['message']);
                        }
                    }
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => __('Wallet not found'),
                    'data' => ''
                ];
            }
        } catch (\Exception $e) {
            $this->logger->log('withdrawalProcess '. $e->getMessage());
            $response = [
                'success' => false,
                'message' => __('Something went wrong'),
                'data' => ''
            ];
        }

        return $response;
    }

    // kyc validation check
    public function kycValidationCheck($userId)
    {
        $response = [
            'success' => true,
            'message' => __('success ')
        ];
        if (settings('kyc_enable_for_withdrawal') == STATUS_ACTIVE) {
            if (settings('kyc_nid_enable_for_withdrawal') == STATUS_ACTIVE) {
                $checkNid = checkUserKyc($userId, KYC_NID_REQUIRED, __('withdrawal '));
                if ($checkNid['success'] == false) {
                    $response = [
                        'success' => false,
                        'message' => $checkNid['message']
                    ];
                    return $response;
                } else {
                    $response = [
                        'success' => true,
                        'message' => __('success ')
                    ];
                }
            }
            if(settings('kyc_passport_enable_for_withdrawal') ==  STATUS_ACTIVE) {
                $checkPass = checkUserKyc($userId, KYC_PASSPORT_REQUIRED, __('withdrawal '));
                if ($checkPass['success'] == false) {
                    $response = [
                        'success' => false,
                        'message' => $checkPass['message']
                    ];
                    return $response;
                } else {
                    $response = [
                        'success' => true,
                        'message' => __('success ')
                    ];
                }
            }
            if(settings('kyc_driving_enable_for_withdrawal') ==  STATUS_ACTIVE) {
                $checkDrive = checkUserKyc($userId, KYC_DRIVING_REQUIRED, __('withdrawal '));
                if ($checkDrive['success'] == false) {
                    $response = [
                        'success' => false,
                        'message' => $checkDrive['message']
                    ];
                    return $response;
                } else {
                    $response = [
                        'success' => true,
                        'message' => __('success ')
                    ];
                }
            }
        } else {
            $response = [
                'success' => true,
                'message' => __('success ')
            ];
        }

        return $response;
    }

    // send coin with bitgo
    public function sendCoinWithBitgo($transaction)
    {
        try {
            $coin = Coin::join('coin_settings','coin_settings.coin_id', '=', 'coins.id')
                ->where(['coins.coin_type' => $transaction->coin_type])
                ->select('coins.*', 'coin_settings.*')
                ->first();
            if ($coin) {
                $currency =  !empty($transaction->network_type) ? $transaction->network_type : $transaction->coin_type;
                $response = $this->sendBitgoCoin($currency,$coin->bitgo_wallet_id,$transaction->amount,$transaction->address,decryptId($coin->bitgo_wallet));
            } else {
                $response = [
                    'success' => false,
                    'message' => __('Coin not found'),
                    'data' => ''
                ];
            }
        } catch (\Exception $e) {
            storeException('sendCoinWithBitgo', $e->getMessage());
            $response = [
                'success' => false,
                'message' => __('Something went wrong'),
                'data' => ''
            ];
        }
        return $response;
    }

    // send bitgo coins
    public function sendBitgoCoin($coinType,$walletId,$amount,$address,$walletPassphrase)
    {
        try {
            $bitgoService = new BitgoWalletService();
            $bitgoResponse = $bitgoService->sendCoinsWithBitgo($coinType,$walletId,$amount,$address,$walletPassphrase);
            $this->logger->log('send coin api response', json_encode($bitgoResponse));

            if ($bitgoResponse['success'] == true) {
                $response = [
                    'success' => true,
                    'message' => __('Coin send successful'),
                    'data' => $bitgoResponse['data']['txid'],
                ];
            } else {
                $this->logger->log('Bitgo sendCoin', $bitgoResponse['message']);
                $response = [
                    'success' => false,
                    'message' => $bitgoResponse['message'],
                    'data' => ""
                ];
            }

        } catch (\Exception $e) {
            $this->logger->log('sendBitgoCoin', $e->getMessage());
            $response = [
                'success' => false,
                'message' => __('Something went wrong'),
                'data' => ""
            ];
        }
        return $response;
    }

    // send coin with erc20 api
    public function sendCoinWithERC20($transaction)
    {
        try {
            $coin = Coin::join('coin_settings','coin_settings.coin_id', '=', 'coins.id')
                ->where(['coins.coin_type' => $transaction->coin_type])
                ->select('coins.*', 'coin_settings.*')
                ->first();
            if ($coin) {
                $coinApi = new ERC20TokenApi($coin);
                $requestData = [
                    "amount_value" => (float)$transaction->amount,
                    "from_address" => $coin->wallet_address,
                    "to_address" => $transaction->address,
                    "contracts" => decryptId($coin->wallet_key)
                ];
                $result = $coinApi->sendCustomToken($requestData);
                storeException('sendCoinWithERC20', json_encode($result));
                if ($result['success'] ==  true) {
                    $data['transaction_id'] = $result['data']->hash;
                    $data['used_gas'] = $result['data']->used_gas;
                    $response = [
                        'success' => true,
                        'message' => __('Coin sent successfully'),
                        'data' => $data
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => __('Coin send failed'),
                        'data' => []
                    ];
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => __('Coin not found'),
                    'data' => []
                ];
            }
        } catch (\Exception $e) {
            storeException('sendCoinWithERC20', $e->getMessage());
            $response = [
                'success' => false,
                'message' => __('Something went wrong'),
                'data' => []
            ];
        }
        return $response;
    }

}
