<?php
namespace App\Http\Services;

class ERC20TokenApi {
    private $chainNetwork = '';
    private $headerKey = '';
    private $nodeUrl = '';
    private $settings;
    private $contractAddress;
    private $decimalValue;
    private $gasLimit;
    private $prevBlockCount;

    public function __construct($coin)
    {
        $this->settings = allsetting();
        $this->chainNetwork = $coin->chain_link ?? '';
        $this->headerKey = $this->settings['erc20_app_key'] ?? '32c412e1f281fea2c93fd972a212040b692b43523';
        $this->nodeUrl = $this->settings['erc20_app_url'] ?? 'http://localhost:8934/';
        $this->contractAddress = $coin->contract_address ?? '';
        $this->decimalValue = $coin->contract_decimal ?? 18;
        $this->gasLimit = $coin->gas_limit ?? 43000;
        $this->prevBlockCount = isset($this->settings['previous_block_count']) ? intval($this->settings['previous_block_count']) : 5000;
    }

    public function apiCall($endPoint,$requestData = null,$type = '')
    {
        try {
            $header = array();
//        $header[] = 'Content-length: 0';
            $header[] = 'Content-type: application/json; charset=utf-8';
            $header[] = 'Accept: application/json';
            $header[] = 'headerkeys:'.$this->headerKey;
            $header[] = 'chainlinks:'.$this->chainNetwork;

            $node_url = $this->nodeUrl.$endPoint;
            $postData =  !empty($requestData) ? json_encode($requestData) : "";
            $ch = curl_init($node_url);
            // set URL and other appropriate options
            curl_setopt($ch, CURLOPT_URL, $node_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

            //create the multiple cURL handle
            $mh = curl_multi_init();

            curl_multi_add_handle($mh,$ch);

            //execute the multi handle
            do {
                $status = curl_multi_exec($mh, $active);
                if ($active) {
                    curl_multi_select($mh);
                }
            } while ($active && $status == CURLM_OK);

            //close the handles
            curl_multi_remove_handle($mh, $ch);
            curl_multi_close($mh);

            // all of our requests are done, we can now access the results
            $responseData = curl_multi_getcontent($ch);

            if ($responseData != "") {
                $result = json_decode($responseData);

                $response = ['success' => $result->status, 'message' => $result->message, 'data' => $result->data ?? '' ];
            } else {
                $response = ['success' => false, 'message' => __('Api response failed'), 'data' =>'' ];
            }
        } catch (\Exception $e) {
            storeException('node apiCall',$e->getMessage());
            $response = ['success' => false, 'message' => $e->getMessage(), 'data' =>'' ];
        }
        return $response;
    }

    //get wallet balance
    /*[▼
    "success" => true
    "message" => "process successfully"
    "data" => { ▼
        "net_balance": "1.472266317"
        "token_balance": 0
    }
    ]*/
    public function checkWalletBalance($requestData)
    {
        try {
            $data = array_merge($requestData,[
                'contract_address' => $this->contractAddress
            ]);
            $response = $this->apiCall('check-wallet-balance',$data,'POST');
        } catch (\Exception $e) {
            storeException('apiCall checkWalletBalance ', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' =>'' ];
        }
        return $response;
    }

    // create new wallet
    /*[▼
    "success" => true
    "message" => "Wallet created successfully"
    "data" => { ▼
        "address": "0xda786857cDaA280d6f168F2E117fA5017a4Ec179"
        "privateKey": "0x8b814548f778ee84cd8ebd6a54njk534n5kj2904ed8ab6c923ed1294fb5dd"
    }
    ]*/
    public function createNewWallet()
    {
        try {
            $response = $this->apiCall('create-wallet','','POST');
        } catch (\Exception $e) {
            storeException('apiCall createNewWallet ', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' =>'' ];
        }
        return $response;
    }

    //check gas fess
    /*[▼
    "success" => true
    "message" => "Get Estimate gas successfully"
    "data" => {
        "gasLimit": 216200
        "amount": "3000000"
        "tx": {#1781 ▶}
        "gasPrice": "0.00000001"
        "estimateGasFees": 0.00037617
    }
    ]*/
    public function checkEstimateGas($requestData)
    {
        try {
            $data = array_merge($requestData,[
                'contract_address' => $this->contractAddress,
                'gas_limit' => $this->gasLimit,
                'decimal_value' => $this->decimalValue
            ]);

            $response = $this->apiCall('check-estimate-gas',$data,'POST');
        } catch (\Exception $e) {
            storeException('apiCall checkEstimateGas ', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' =>'' ];
        }
        return $response;
    }

    // send custom token
    /*[▼
    "success" => true
    "message" => "Token sent successfully"
    "data" => {
        "hash": "0x2b55fe016b57203db5841cb534a4a4ff96d9a771d5fcd602cc75428f38fcddd9"
        "used_gas": 0.00037617
    "tx": {
        "blockHash": "0x2f8838e95b9c2f2b8b9a00cc82c881fd2ba15876c3f633ac6a6f6d4023edca4f"
        "blockNumber": 16178983
        "contractAddress": null
        "cumulativeGasUsed": 1882650
        "from": "0xf2df582ab8ba0c7e57e897ca3371aabb68648ca8"
        "gasUsed": 37617
        "logs": array:1 [▶]
        "logsBloom": "0x00000000000000000000000000000000000000000000000000001000000000000000000000000000000000000000000000000000000000080000000000000000000000000000000000000008000000 ▶"
        "status": true
        "to": "0x2752eee959596ced6cfea51862ca9f6cf6e46745"
        "transactionHash": "0x2b55fe016b57203db5841cb534a4a4ff96d9a771d5fcd602cc75428f38fcddd9"
        "transactionIndex": 11
        "type": "0x0"
     }
    }
    ]*/
    public function sendCustomToken($requestData)
    {
        try {
            $data = array_merge($requestData,[
                'contract_address' => $this->contractAddress,
                'gas_limit' => $this->gasLimit,
                'decimal_value' => $this->decimalValue
            ]);

            $response = $this->apiCall('send-token',$data,'POST');
            storeException('apiCall sendCustomToken ', json_encode($response));
        } catch (\Exception $e) {
            storeException('apiCall sendCustomToken ', $e->getMessage());
            $response = ['success' => false, 'message' => $e->getMessage(), 'data' =>'' ];
        }
        return $response;
    }

    // send eth or bnb
    /*[▼
    "success" => true
    "message" => "Coin sent successfully"
    "data" => { ▼
        "hash": "0x9bc456e9184ada3ad74ec3124b43020059a7732d52e510d7a1058c2acc87d2b2"
    }
    ]*/
    public function sendEth($requestData)
    {
        try {
            $data = array_merge($requestData,[
                'gas_limit' => $this->gasLimit,
                'decimal_value' => $this->decimalValue
            ]);

            $response = $this->apiCall('send-eth',$data,'POST');
        } catch (\Exception $e) {
            storeException('apiCall sendEth ', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' =>'' ];
        }
        return $response;
    }

    // get transaction data
    /*[▼
    "success" => true
    "message" => "get hash"
    "data" => {▼
        "hash": { ▼
            "blockHash": "0x705c17a4b8ca70ed2816cd9fb2c860224c40e6d3b72f6a0a217ed3b35140d6d3"
            "blockNumber": 16164874
            "contractAddress": null
            "cumulativeGasUsed": 8174988
            "from": "0xf2df582ab8ba0c7e57e897ca3371aabb68648ca8"
            "gasUsed": 37617
            "logs": array:1 [▶]
            "logsBloom": "0x00000000000000000000000000000000000000000000000000001000000000000000000000000000000000000000000000000000000000080000000000000000000000000000000000000008000000 ▶"
            "status": true
            "to": "0x2752eee959596ced6cfea51862ca9f6cf6e46745"
            "transactionHash": "0x634034c8d7ab3eedf941a2cb961e89da59f0c75950e4919e8959de1e7b9a1730"
            "transactionIndex": 13
            "type": "0x0"
        }
    "gas_used": 0.00037617
      }
    ]*/
    public function getTransactionData($requestData)
    {
        try {
            $response = $this->apiCall('get-transaction-data',$requestData,'POST');
        } catch (\Exception $e) {
            storeException('apiCall getTransactionData ', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' =>'' ];
        }
        return $response;
    }

    // get transfer event for contract address
    /*[▼
    "success" => true
    "message" => "found block details"
    "data" => {▼
        "result": array:2 [▼
            0 => {▼
                "event": "Transfer"
                "signature": "0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef"
                "contract_address": "0x2752eEE959596cED6CfeA51862CA9F6cf6e46745"
                "tx_hash": "0x9d2cbaa58801119497f52defaf2680de9fa2ac5ea065f1425a2e89781ac9c8ee"
                "block_hash": "0x0d859cf36a3a8d04b1aa11a3d09c715e9dda790f7da7332b731986cb1a00430f"
                "from_address": "0xf2DF582ab8bA0C7E57e897Ca3371AAbB68648CA8"
                "to_address": "0xe0eAf3B1eBc93e6f85F94c614122E3C15dADCFf9"
                "amount": "1"
            }
            1 => {#1836 ▶}
                ]
            }
    ]*/
    public function getContractTransferEvent()
    {
        try {
            $requestData = [
                "contract_address" => $this->contractAddress,
                "number_of_previous_block" => $this->prevBlockCount,
                "decimal_value" => $this->decimalValue
            ];
            $response = $this->apiCall('get-transfer-event',$requestData,'POST');
        } catch (\Exception $e) {
            storeException('apiCall getContractTransferEvent ', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' =>'' ];
        }
        return $response;
    }

};



