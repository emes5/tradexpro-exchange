import BankDeposit from "components/deposit/bank-deposit";
import WalletDeposit from "components/deposit/wallet-deposit";
import StripeDeposit from "components/deposit/stripe-deposit";
import {
  BANK_DEPOSIT,
  PAYPAL,
  STRIPE,
  WALLET_DEPOSIT,
} from "helpers/core-constants";
import useTranslation from "next-translate/useTranslation";
import React, { useEffect, useState } from "react";
import { currencyDeposit } from "service/deposit";
import SelectDeposit from "components/deposit/selectDeposit";
import DepositFaq from "components/deposit/DepositFaq";
import { PayPalButtons } from "@paypal/react-paypal-js";
import PaypalButtons from "components/deposit/PaypalDeposit";
import PaypalSection from "components/deposit/PaypalSection";

const Deposit = () => {
  const { t } = useTranslation("common");
  const [loading, setLoading] = useState(false);
  const [selectedMethod, setSelectedMethod] = useState<any>({
    method: null,
    method_id: null,
  });
  const [depositInfo, setDepositInfo] = useState<any>();
  const getDepositInfo = async () => {
    const response = await currencyDeposit();
    setDepositInfo(response.data);
    console.log(response.data, "response.data");
    setSelectedMethod({
      method:
        response?.data?.payment_methods[0] &&
        response?.data?.payment_methods[0].payment_method,
      method_id:
        response?.data?.payment_methods[0] &&
        response?.data?.payment_methods[0].id,
    });
  };
  useEffect(() => {
    getDepositInfo();
  }, []);
  return (
    <div>
      <div className="container mb-3">
        <h2 className="mb-2">{t("Deposit Fiat")}</h2>
      </div>
      <div className="container">
        <div className="deposit-conatiner">
          <div className="cp-user-title">
            <h4>{t("Select method")}</h4>
          </div>
          <SelectDeposit
            setSelectedMethod={setSelectedMethod}
            depositInfo={depositInfo}
            selectedMethod={selectedMethod}
          />
          <div className="row">
            <div className="col-lg-8 col-sm-12">
              {parseInt(selectedMethod.method) === WALLET_DEPOSIT ? (
                <WalletDeposit
                  walletlist={depositInfo.wallet_list}
                  method_id={selectedMethod.method_id}
                />
              ) : parseInt(selectedMethod.method) === BANK_DEPOSIT ? (
                <BankDeposit
                  currencyList={depositInfo.currency_list}
                  walletlist={depositInfo.wallet_list}
                  method_id={selectedMethod.method_id}
                  banks={depositInfo.banks}
                />
              ) : parseInt(selectedMethod.method) === STRIPE ? (
                <StripeDeposit
                  currencyList={depositInfo.currency_list}
                  walletlist={depositInfo.wallet_list}
                  method_id={selectedMethod.method_id}
                  banks={depositInfo.banks}
                />
              ) : parseInt(selectedMethod.method) === PAYPAL ? (
                // <PaypalButtons />
                <PaypalSection
                  currencyList={depositInfo.currency_list}
                  walletlist={depositInfo.wallet_list}
                  method_id={selectedMethod.method_id}
                  banks={depositInfo.banks}
                />
              ) : (
                ""
              )}
            </div>
            <div className="col-lg-4 col-sm-12 mt-4">
              <DepositFaq />
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Deposit;
