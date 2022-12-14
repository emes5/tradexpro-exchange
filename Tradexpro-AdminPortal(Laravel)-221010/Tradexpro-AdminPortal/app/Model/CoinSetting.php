<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoinSetting extends Model
{
    use HasFactory;
    protected $fillable = [
        'coin_id',
        'bitgo_wallet_id',
        'bitgo_deleted_status',
        'bitgo_approvalsRequired',
        'bitgo_wallet_type',
        'bitgo_wallet',
        'chain',
        'webhook_status',
        'coin_api_user',
        'coin_api_pass',
        'coin_api_host',
        'coin_api_port',
        'coin_price',
        'contract_coin_name',
        'chain_link',
        'chain_id',
        'contract_address',
        'wallet_address',
        'wallet_key',
        'contract_decimal',
        'gas_limit',
        'check_encrypt'
    ];
}
