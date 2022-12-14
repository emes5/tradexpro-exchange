import React, { useState } from "react";
import WalletGoogleAuth from "components/wallet/wallet-google-auth";
import { UserSettingsApi } from "service/settings";
import { formateZert } from "common";
import useTranslation from "next-translate/useTranslation";

const WirhdrawTab = ({ response, TurnoffSetShow }: any) => {
  const [selectedNetwork, setSelectedNetwork] = useState(
    response?.data && response?.data[0]
  );
  const { t } = useTranslation("common");
  const [withdrawalCredentials, setWithdrawalCredentials] = React.useState({
    wallet_id: response?.wallet?.id,
    code: "",
    address: "",
    amount: "",
    note: "withdrawal",
    network_type: selectedNetwork?.network_type ?? "",
  });
  const [errorMessage, setErrorMessage] = React.useState({
    status: false,
    message: "",
  });
  const CheckG2faEnabled = async () => {
    const { data } = await UserSettingsApi();
    const { user } = data;
    if (user.google2fa === 1) {
    } else {
      setErrorMessage({
        status: true,
        message: "Google 2FA is not enabled, Please enable Google 2FA fist",
      });
    }
  };
  React.useEffect(() => {
    setWithdrawalCredentials({
      ...withdrawalCredentials,
      wallet_id: response?.wallet?.id,
    });

    CheckG2faEnabled();
  }, [response?.wallet?.id]);

  React.useEffect(() => {
    setWithdrawalCredentials({
      ...withdrawalCredentials,
      network_type: selectedNetwork?.network_type,
    });
  }, [selectedNetwork?.network_type]);

  return (
    <div className="asset-balances-right visible mb-2">
      <div className="box-one single-box">
        <div className="section-wrapper">
          <div className="deposit-info-area" id="wallet_deposit_area"></div>
        </div>
      </div>
      <div className="box-two single-box visible">
        <div className="section-wrapper">
          <div className="withdrawal-info-area" id="withdrawal_wallet_area">
            <div className="withdrawal-info-top">
              <div className="balance-box">
                <img
                  className="icon"
                  src={response?.wallet?.coin_icon || "/bitcoin.png"}
                  alt="coin"
                />
                <div className="balance-content">
                  <h4>
                    {response?.wallet?.coin_type} {t("Balance")}
                  </h4>
                  <h5>
                    {response?.wallet?.coin_type} {t("Wallet")}
                  </h5>
                </div>
              </div>
              <a
                href="#"
                className="close-btn"
                onClick={() => {
                  TurnoffSetShow();
                }}
              >
                <i className="fa fa-times" />
              </a>
            </div>
            <div className="withdrawal-form">
              <div className="avable-blance">
                <h4 className="avable-blance-title">
                  {t("AVAILABLE BALANCE")}
                </h4>
                <h2 className="blance">
                  {formateZert(response?.wallet?.balance)}{" "}
                  {response?.wallet?.coin_type}
                </h2>
              </div>
              <form>
                {response.wallet.coin_type == "USDT" && (
                  <div className="total-balance">
                    <select
                      name="currency"
                      className="form-control"
                      onChange={(e) => {
                        const findObje = response?.data?.find(
                          (x: any) => x.id === parseInt(e.target.value)
                        );
                        setSelectedNetwork(findObje);
                      }}
                    >
                      {response?.data?.map((item: any, index: number) => (
                        <option value={item.id} key={index}>
                          {item?.network_name}
                        </option>
                      ))}
                    </select>
                  </div>
                )}
                <div className="form-group">
                  <input
                    type="text"
                    className="form-control"
                    id="address"
                    name="address"
                    placeholder="Address"
                    value={withdrawalCredentials.address}
                    onChange={(e) => {
                      setWithdrawalCredentials({
                        ...withdrawalCredentials,
                        address: e.target.value,
                      });
                    }}
                  />
                </div>
                <div className="form-group">
                  <div className="amount-wrap">
                    <input
                      type="text"
                      className="form-control"
                      id="amountWithdrawal"
                      name="amount"
                      placeholder="AMOUNT TO WITHDRAW"
                      value={withdrawalCredentials.amount}
                      onChange={(e) => {
                        setWithdrawalCredentials({
                          ...withdrawalCredentials,
                          amount: e.target.value,
                        });
                      }}
                    />
                    <small>
                      <span className="mr-2">
                        Fees
                        {parseFloat(response?.wallet?.withdrawal_fees).toFixed(
                          8
                        )}{" "}
                        %
                      </span>
                      <span className="mr-2">
                        Min withdraw{" "}
                        {parseFloat(
                          response?.wallet?.minimum_withdrawal
                        ).toFixed(5)}
                        {response?.wallet?.coin_type}
                      </span>
                      <span className="mr-2">
                        Max withdraw{" "}
                        {parseFloat(response?.wallet?.maximum_withdrawal)}{" "}
                        {response?.wallet?.coin_type}
                      </span>
                    </small>
                  </div>
                </div>
                <WalletGoogleAuth
                  withdrawalCredentials={withdrawalCredentials}
                  setWithdrawalCredentials={setWithdrawalCredentials}
                />
                <input type="hidden" name="wallet_id" value="19" />
                {errorMessage.status && (
                  <div className="alert alert-danger">
                    {errorMessage.message}
                  </div>
                )}

                <button
                  type="button"
                  className="withdraw-btn"
                  data-target="#exampleModal"
                  disabled={
                    withdrawalCredentials.address === "" ||
                    withdrawalCredentials.amount === "" ||
                    errorMessage.status === true
                  }
                  data-toggle="modal"
                  onClick={() => {
                    setErrorMessage({
                      status: false,
                      message: "",
                    });
                  }}
                >
                  {t("Withdraw")}
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
export default WirhdrawTab;
