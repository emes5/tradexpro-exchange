<?php

namespace App\Http\Services;



use App\Model\WalletAddressHistory;

class wallet
{
  function AddWalletAddressHistory($wallet_id,$address,$coin_type,$wallet_key)
  {
      if(!empty($wallet_key)) {
          $wallet_key = STRONG_KEY.$address.$wallet_key;
      }
       WalletAddressHistory::firstOrCreate(['wallet_id' => $wallet_id,'coin_type' => $coin_type],[
           'address' => $address,
           'wallet_key' => $wallet_key
       ]);
       return ['success'=>true];
}
}
