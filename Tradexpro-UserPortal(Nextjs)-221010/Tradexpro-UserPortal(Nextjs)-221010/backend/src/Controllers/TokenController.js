const { response } = require("express");
const Web3 = require("web3");
const {contractJson} = require("../../src/ContractAbiJson");
const { contract_decimals } = require("../Heplers/helper");


function getData(req, res)
{
    res.send({
        status: true,
        message: "data successfully",
        data: {
            'data' : 'exapmle data '
        }
    });
}

/*{
    address: '0x33B380d0b8B1e5Bc3Efb364FCf4eaEA46834Eb96',
    privateKey: '0x32ddeae1c7302f484e35a53685edab78147a78757bba755d5094497f60307fce',
    signTransaction: [Function: signTransaction],
    sign: [Function: sign],
    encrypt: [Function: encrypt]
}*/

async function generateAddress(req, res) 
{
    try {
        const network = req.headers.chainlinks;
        if (network) {
            const connectWeb3 = new Web3(new Web3.providers.HttpProvider(network));
            
            let wallet = await connectWeb3.eth.accounts.create();
            if (wallet) {
                res.json({
                    status: true,
                    message: "Wallet created successfully",
                    data: wallet
                });
            } else {
                res.json({
                    status: false,
                    message: "Wallet not generated",
                    data: {}
                });
            }
        } else {
            res.json({
                status: false,
                message: "No chain provided",
                data: {}
            });
        }
    } catch(e){
        res.json({
            status: false,
            message: e.message,
            data: {}
        });
    }
    
}

async function getWalletBalance(req, res)
{
    try {
        const network = req.headers.chainlinks;
        let contractJsons = contractJson();
        
        if (network) {
            const type = req.body.type;
            const address = req.body.address;
            let netBalance = 0;
            let tokenBalance = 0;
            const web3 = new Web3(network);

            if (type == 1) {
                netBalance = await web3.eth.getBalance(address);
                netBalance = Web3.utils.fromWei(netBalance.toString(), 'ether');

            } else if(type == 2) {
                const contractAddress = req.body.contract_address;
                if (contractAddress) {
                    const contractInstance = new web3.eth.Contract(contractJsons, contractAddress);
                    tokenBalance = await contractInstance.methods.balanceOf(address).call();
                    tokenDecimal = await contractInstance.methods.decimals().call();
                    tokenBalance = Web3.utils.fromWei(tokenBalance.toString(), contract_decimals(tokenDecimal));
                } else {
                    res.json({
                        status: false,
                        message: "Contract address required",
                        data: {}
                    });
                } 

            } else {
                const contractAddress = req.body.contract_address;
                if (contractAddress) {
                    netBalance = await web3.eth.getBalance(address);
                    netBalance = Web3.utils.fromWei(netBalance.toString(), 'ether');

                    const contractInstance = new web3.eth.Contract(contractJsons, contractAddress);
                    tokenBalance = await contractInstance.methods.balanceOf(address).call();
                    tokenDecimal = await contractInstance.methods.decimals().call();
                    tokenBalance = Web3.utils.fromWei(tokenBalance.toString(), contract_decimals(tokenDecimal));

                } else {
                    res.json({
                        status: false,
                        message: "Contract address required",
                        data: {}
                    });
                } 
            }
            const data = {
                net_balance : netBalance,
                token_balance : tokenBalance
            }

            res.send({
                status: true,
                message: "process successfully",
                data: data
            });

        } else {
            res.json({
                status: false,
                message: "No chain provided",
                data: {}
            });
        }
    } catch(e){
        res.json({
            status: false,
            message: e.message,
            data: {}
        });
    }
}

async function checkEstimateGasFees(req, res)
{
    try {
        const network = req.headers.chainlinks;
        let contractJsons = contractJson();
       
        if (network) {
            const fromAddress = req.body.from_address;
            const contractAddress = req.body.contract_address;
            const receiverAddress = req.body.to_address;
            const gasLimit = req.body.gas_limit;
            const decimalValue = contract_decimals(req.body.decimal_value);
            let amount = req.body.amount_value;

                const web3 = new Web3(network);
                const contract = new web3.eth.Contract(contractJsons, contractAddress);
    
                amount = Web3.utils.toWei(amount.toString(), decimalValue);

                const tx = {
                    from: fromAddress,
                    to: contractAddress,
                    gas: gasLimit,
                    data:  contract.methods.transfer(receiverAddress, amount).encodeABI(),
                };
                
    
                let gasPrice =  await web3.eth.getGasPrice();
                let estimateGas =  await web3.eth.estimateGas(tx);
                
                gasPrice = Web3.utils.fromWei(gasPrice.toString(), 'ether')
               
                estimateGas = estimateGas * gasPrice

                res.json({
                    status: true,
                    message: "Get Estimate gas successfully",
                    data: {
                        gasLimit : gasLimit,
                        amount : amount,
                        tx: tx,
                        gasPrice: gasPrice,
                        estimateGasFees: estimateGas
                    }
                });
            
        } else {
            res.json({
                status: false,
                message: "No chain provided",
                data: {}
            });
        }
    } catch(e) {
        res.json({
            status: false,
            message: e.message,
            data: {}
        });
    }
}

 async function sendToken(req, res)
{
    try {
        const network = req.headers.chainlinks;
        let contractJsons = contractJson();
       
        if (network) {
            const fromAddress = req.body.from_address;
            const contractAddress = req.body.contract_address;
            const receiverAddress = req.body.to_address;
            const gasLimit = req.body.gas_limit;
            const decimalValue = contract_decimals(req.body.decimal_value);
            const privateKey = req.body.contracts;
            let amount = req.body.amount_value;

            let checkValidAddress = new Web3().utils.isAddress(receiverAddress);
            
            if (checkValidAddress){
                const web3 = new Web3(network);
                let gasPrice =  await web3.eth.getGasPrice();
                gasPrice = Web3.utils.fromWei(gasPrice.toString(), 'ether');

                const contract = new web3.eth.Contract(contractJsons, contractAddress);
    
                amount = Web3.utils.toWei(amount.toString(), decimalValue);

                const tx = {
                    from: fromAddress,
                    to: contractAddress,
                    gas: gasLimit,
                    data:  contract.methods.transfer(receiverAddress, amount).encodeABI(),
                };
                

               await web3.eth.accounts.signTransaction(tx, privateKey).then(signed => {
                    const tran = web3.eth
                    .sendSignedTransaction(signed.rawTransaction)
                    .on('confirmation', (confirmationNumber, receipt) => {
    
                    })
                    .on('transactionHash', hash => {
                    })
                    .on('receipt', receipt => {
                        // var receipt = {
                        //     hash:receipt.transactionHash,
                        // };
                        // const hashDetails = getHashTransaction(network,receipt.hash);
                        res.json({
                            status: true,
                            message: "Token sent successfully",
                            data: {
                                hash : receipt.transactionHash,
                                used_gas: receipt.gasUsed * gasPrice,
                                tx : receipt
                            }
                        });
                    })
                    .on('error',function (error){  
                        res.json({
                            status: false,
                            message: error.toString(),
                            data:  {}
                        });
                    });
                });
            } else {
                res.json({
                    status: false,
                    message: "Invalid address",
                    data: {}
                });
            }
            
        } else {
            res.json({
                status: false,
                message: "No chain provided",
                data: {}
            });
        }
    } catch(e) {
        console.log(e)
        res.json({
            status: false,
            message: e.message,
            data: {}
        });
    }
}

async function getDataByTransactionHash(req, res)
{
    try {
        const network = req.headers.chainlinks;
        const hash = req.body.transaction_hash;

        const response = await getHashTransaction(network,hash);
        res.send({
            status: response.status,
            message: response.message,
            data: response.data
        })
    } catch (e) {
        res.send({
            status: false,
            message: e.message,
            data: {}
        })
    }

}

async function getHashTransaction(network,hash)
{
    try {       
        const transactionHash = hash
        if (network) {
            const web3 = new Web3(network);
            let gasPrice =  await web3.eth.getGasPrice();
            gasPrice = Web3.utils.fromWei(gasPrice.toString(), 'ether')
            const hash = await web3.eth.getTransactionReceipt(transactionHash)
            if (hash) {
                return {
                    status: true,
                    message: "get hash",
                    data: {
                        hash: hash,
                        gas_used: hash.gasUsed * gasPrice
                    }
                };
            } else {
                return {
                    status: false,
                    message: "not found",
                    data: {} 
                };
            }
            
        } else {
            return{
                status: false,
                message: "No chain provided",
                data: {}
            };
        }
    } catch(e) {
        return{
            status: false,
            message: e.message,
            data: {}
        };
    }
}

async function sendEth(req, res)
{
    try {
        const network = req.headers.chainlinks;
       
        if (network) {
            const fromAddress = req.body.from_address;
            const receiverAddress = req.body.to_address;
            const gasLimit = req.body.gas_limit;
            const decimalValue = contract_decimals(req.body.decimal_value);
            const privateKey = req.body.contracts;
            let amount = req.body.amount_value;

            const web3 = new Web3(network);
            let checkValidAddress = new Web3().utils.isAddress(receiverAddress);
            
            if (checkValidAddress){
                amount = Web3.utils.toWei(amount.toString(), 'ether');
                let gasPrice =  await web3.eth.getGasPrice();
                let nonce = await web3.eth.getTransactionCount(fromAddress,'latest');
                
                let transaction = {
                from: fromAddress,
                nonce: web3.utils.toHex(nonce),
                gas: gasLimit,
                to: receiverAddress,
                value: amount,
                // chainId: chainId // 
                };

                const signedTx = await web3.eth.accounts.signTransaction(transaction, privateKey);

                web3.eth.sendSignedTransaction(signedTx.rawTransaction, function(error, hash) {
                    if (!error) {
                        res.json({
                            status: true,
                            message: "Coin sent successfully",
                            data: {
                                hash : hash,
                            }
                        });

                    } else {
                      res.json({
                            status: false,
                            message: "Sending failed",
                            data: {
                                error
                            }
                        });
                    }
                   });
            } else {
                res.json({
                    status: false,
                    message: "Invalid address",
                    data: {}
                });
            }
            
        } else {
            res.json({
                status: false,
                message: "No chain provided",
                data: {}
            });
        }
    } catch(e) {
        res.json({
            status: false,
            message: e.message
        });
    }
}

async function getTransactionByContractAddress(req, res) 
{
    try {
        const network = req.headers.chainlinks;
        let contractJsons = contractJson();
       
        if (network) {
            const contractAddress = req.body.contract_address;

            const web3 = new Web3(network);
            // const contract = new web3.eth.Contract(contractJsons, contractAddress);
            // console.log(contract)
            const getBlockNumber = await web3.eth.getBlockNumber();
            // console.log(getBlock)
            const block = await getBlockData(web3,getBlockNumber);
            // const tx = await getTransactionsByAccount(web3,contractAddress,getBlock, null);
            // console.log(tx);
            res.json({
                status: true,
                message: "get block",
                data: {
                    blockNumber: getBlockNumber,
                    block: block,
                    
                }
            });
            
        } else {
            res.json({
                status: false,
                message: "No chain provided",
                data: {}
            });
        }
    } catch(e) {
        res.json({
            status: false,
            message: e.message
        });
    }
}

async function getBlockData(web3,blockNumber)
{
    try {
        const block = await web3.eth.getBlock(blockNumber)
        if (block) {
            return {
                status: true,
                message: 'success',
                data: block
            };
        } else {
            return {
                status: false,
                message: 'failed',
                data: {}
            };
        }

    } catch (e) {
        console.log(e);
    }
}

async function getTransactionsByAccount(web3,myaccount, endBlockNumber,startBlockNumber) 
{
    
    try {
        console.log("Using endBlockNumber: " + endBlockNumber);

        if (startBlockNumber == null) {
          startBlockNumber = endBlockNumber - 5;
        //   console.log("Using startBlockNumber: " + startBlockNumber);
        }
        // console.log("Searching for transactions to/from account \"" + myaccount + "\" within blocks "  + startBlockNumber + " and " + endBlockNumber);
      
        let tx = [];
        for (let i = startBlockNumber; i <= endBlockNumber; i++) {
            // console.log(startBlockNumber +' '+ endBlockNumber)
          if (i % 1000 == 0) {
            console.log("Searching block " + i.toString());
          }
       
          let block = await web3.eth.getBlock(i);
        //   console.log(block);
          if (block != null && block.transactions != null) {
            block.transactions.forEach(async function(e) {
               let trx = await web3.eth.getTransactionReceipt(e)
               console.log(trx)
            //   if (myaccount == "*" || myaccount == e.from || myaccount == e.to) {
            //       tx.push(e);
            //     console.log("  tx hash          : " + e.hash + "\n"
            //       + "   nonce           : " + e.nonce + "\n"
            //       + "   blockHash       : " + e.blockHash + "\n"
            //       + "   blockNumber     : " + e.blockNumber + "\n"
            //       + "   transactionIndex: " + e.transactionIndex + "\n"
            //       + "   from            : " + e.from + "\n" 
            //       + "   to              : " + e.to + "\n"
            //       + "   value           : " + e.value + "\n"
            //       + "   time            : " + block.timestamp + " " + new Date(block.timestamp * 1000).toGMTString() + "\n"
            //       + "   gasPrice        : " + e.gasPrice + "\n"
            //       + "   gas             : " + e.gas + "\n"
            //       + "   input           : " + e.input);
            //   }
            })
          }
        }
    } catch(err) {
        console.log('exception' +err.message);
    }
    
  }

async function getLatestEvents(req, res)
{
  try {
      const network = req.headers.chainlinks;
      let contractJsons = contractJson();

      if (network) {
          let prevBlock = 1000;
          const contractAddress = req.body.contract_address;
          const numberOfBlock = req.body.number_of_previous_block;
          const decimalValue = contract_decimals(req.body.decimal_value);

          const web3 = new Web3(new Web3.providers.HttpProvider(network));
          const contract = new web3.eth.Contract(contractJsons, contractAddress);
          // console.log(contract)
          const latestBlockNumber = await web3.eth.getBlockNumber();
          if (numberOfBlock) {
              prevBlock = numberOfBlock;
          }
          const fromBlockNumber = latestBlockNumber - prevBlock;
          const result = await getBlockDetails(contract,fromBlockNumber,latestBlockNumber);

          if (result.status === true) {
              let resultData = [];
              result.data.forEach(function (res) {
                  let innerData = {
                      event: res.event,
                      signature: res.signature,
                      contract_address: res.address,
                      tx_hash: res.transactionHash,
                      block_hash: res.blockHash,
                      from_address: res.returnValues.from,
                      to_address: res.returnValues.to,
                      amount: Web3.utils.fromWei(res.returnValues.value.toString(), decimalValue)
                  };
                  resultData.push(innerData)
              });
              res.json({
                  status: true,
                  message: result.message,
                  data: {
                      result: resultData,
                  }
              });
          } else {
              res.json({
                  status: false,
                  message: result.message,
                  data: {}
              });
          }

      } else {
          res.json({
              status: false,
              message: "No chain provided",
              data: {}
          });
      }
  } catch(e) {
      res.json({
          status: false,
          message: e.message
      });
  }
}
async function getBlockDetails(contract,fromBlockNumber,toBlockNumber)
{
  try {
      const response = await contract.getPastEvents("Transfer",
          {
              fromBlock: fromBlockNumber,
              toBlock: toBlockNumber // You can also specify 'latest'
          });
          // .then(events => {
          //     console.log(events)
          //         return {
          //             status: true,
          //             message: " block details",
          //             data: {event: events}
          //         };
          //     }
          // )
          // .catch((err) => {
          //     return {
          //         status: false,
          //         message: err.message,
          //         data: {}
          //     }
          // });

      if (response && response.length > 0) {
          return {
              status: true,
              message: "found block details",
              data: response
          };
      } else {
          return {
              status: false,
              message: " no data found",
              data: []
          };
      }
  } catch (e) {
      return {
          status: false,
          message: e.message,
          data:[]
      }
  }
}

module.exports = {
    getData,
    generateAddress,
    getWalletBalance,
    sendEth,
    sendToken,
    checkEstimateGasFees,
    getTransactionByContractAddress,
    getDataByTransactionHash,
    getLatestEvents
}