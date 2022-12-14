const express = require("express");

const {
        getData,
        generateAddress, 
        getWalletBalance, 
        sendToken, 
        checkEstimateGasFees, 
        sendEth, 
        getTransactionByContractAddress,
        getDataByTransactionHash,
        getLatestEvents
    } = require("../Controllers/TokenController");
    
const { checkSecurity } = require("../middleware/common/SecurityCheck");
const { CheckBalanceValidators, CheckBalanceValidatorHandler } = require("../Validator/GetBalanceValidator");

const route = express.Router();


route.use(checkSecurity)
route.get("/",getData);
route.post("/create-wallet",generateAddress);
route.post("/check-wallet-balance",CheckBalanceValidators,CheckBalanceValidatorHandler,getWalletBalance);
route.post("/send-eth",sendEth);
route.post("/send-token",sendToken);
// route.post("/get-contract-transaction", getTransactionByContractAddress)
route.post("/check-estimate-gas", checkEstimateGasFees);
route.post("/get-transaction-data", getDataByTransactionHash);
route.post("/get-transfer-event", getLatestEvents);

module.exports = route;