import useTranslation from "next-translate/useTranslation";
import { useRouter } from "next/router";
import React, { useEffect, useState } from "react";
import { toast } from "react-toastify";
import {
  currencyDepositProcess,
  getCurrencyDepositRate,
} from "service/deposit";

const WalletDeposit = ({ walletlist, method_id }: any) => {
  const { t } = useTranslation("common");
  const router = useRouter();
  const [credential, setCredential] = useState<any>({
    wallet_id: null,
    payment_method_id: method_id ? method_id : null,
    amount: 0,
    from_wallet_id: null,
  });
  const [calculatedValue, setCalculatedValue] = useState<any>({
    calculated_amount: 0,
    rate: 0,
  });
  const [available, setAvailable] = useState<any>(0);
  const getCurrencyRate = async () => {
    if (
      credential.wallet_id &&
      credential.payment_method_id &&
      credential.amount &&
      credential.from_wallet_id
    ) {
      const response = await getCurrencyDepositRate(credential);
      setCalculatedValue(response.data);
    }
  };
  const convertCurrency = async () => {
    if (
      credential.wallet_id &&
      credential.payment_method_id &&
      credential.amount &&
      credential.from_wallet_id
    ) {
      const res = await currencyDepositProcess(credential);
      if (res.success) {
        toast.success(res.message);
        router.push("/user/currency-deposit-history");
      } else {
        toast.error(res.message);
      }
    } else {
      toast.error(t("Select all the fields"));
    }
  };
  useEffect(() => {
    getCurrencyRate();
  }, [credential]);
  return (
    <div>
      <div className="cp-user-title mt-5 mb-4">
        <h4>{t("Wallet list")}</h4>
      </div>
      <div className="row">
        <div className="col-lg-12">
          <div className="">
            <div className="swap-area">
              <div className="swap-area-top">
                <div className="form-group">
                  <div className="swap-wrap">
                    <div className="swap-wrap-top">
                      <label>{t("From")}</label>
                      <span className="available">
                        {t("Available Balance: ")}
                        {parseFloat(available)}
                      </span>
                    </div>
                    <div className="swap-input-wrap">
                      <div className="form-amount">
                        <input
                          type="number"
                          className="form-control"
                          id="amount-one"
                          placeholder={t("Please enter 10 -2400000")}
                          onChange={(e) => {
                            setCredential({
                              ...credential,
                              amount: e.target.value,
                            });
                          }}
                        />
                      </div>
                      <div className="cp-select-area">
                        <select
                          className="form-control "
                          id="currency-one"
                          onChange={(e) => {
                            setCredential({
                              ...credential,
                              from_wallet_id: parseInt(e.target.value),
                            });
                            setAvailable(
                              walletlist.find(
                                (wallet: any) =>
                                  parseInt(wallet.id) ===
                                  parseInt(e.target.value)
                              ).balance
                            );
                          }}
                        >
                          <option value="" selected disabled hidden>
                            Select one
                          </option>
                          {walletlist.map((wallet: any, index: any) => (
                            <option value={wallet.id} key={index}>
                              {wallet.coin_type}
                            </option>
                          ))}
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div className="col-lg-12">
          <div className="">
            <div className="swap-area">
              <div className="swap-area-top">
                <div className="form-group">
                  <div className="swap-wrap">
                    <div className="swap-wrap-top">
                      <label>{t("To")}</label>
                    </div>
                    <div className="swap-input-wrap">
                      <div className="form-amount">
                        <input
                          type="text"
                          className="form-control"
                          id="amount-one"
                          disabled
                          value={calculatedValue.calculated_amount}
                          placeholder={t("Please enter 10 -2400000")}
                        />
                      </div>
                      <div className="cp-select-area">
                        <select
                          className=" form-control "
                          id="currency-one"
                          onChange={(e) => {
                            setCredential({
                              ...credential,
                              wallet_id: parseInt(e.target.value),
                            });
                          }}
                        >
                          <option value="" selected disabled hidden>
                            Select one
                          </option>
                          {walletlist.map((wallet: any, index: any) => (
                            <option value={wallet.id} key={index}>
                              {wallet.coin_type}
                            </option>
                          ))}
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <button
            className="primary-btn-outline w-100"
            data-toggle="modal"
            data-target="#exampleModal"
            onClick={convertCurrency}
          >
            Deposit
          </button>
        </div>
      </div>
    </div>
  );
};

export default WalletDeposit;
