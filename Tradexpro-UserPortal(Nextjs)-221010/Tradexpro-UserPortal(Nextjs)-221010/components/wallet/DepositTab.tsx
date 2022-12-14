import React, { useState } from "react";
import Qr from "components/common/qr";
import { copyTextById, formateZert } from "common";
import useTranslation from "next-translate/useTranslation";
import { GetWalletAddressAction } from "state/actions/wallet";

const DepositTab = ({ response, TurnoffSetShow, id }: any) => {
  const { t } = useTranslation("common");
  const [selectedNetwork, setSelectedNetwork] = useState(
    response?.data && response?.data[0]
  );

  return (
    <div className={`asset-balances-right visible mb-3`}>
      <div className={`box-one single-box visible`}>
        <div className="section-wrapper">
          <div className="deposit-info-area" id="wallet_deposit_area">
            <div className="deposit-info-top">
              <div className="balance-box">
                <img
                  className="icon"
                  src={response?.deposit?.coin_icon || "/bitcoin.png"}
                  alt="coin"
                />
                <div className="balance-content">
                  <h4>
                    {response?.deposit?.coin_type} {t("Balance")}
                  </h4>
                  <h5>
                    {response?.deposit?.coin_type} {t("Wallet")}
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
            <div className="total-balance">
              <div className="total-balance-left">
                <h3>{t("Total Balance")}</h3>
              </div>
              <div className="total-balance-right">
                <h3>
                  {formateZert(response?.deposit?.balance)}
                  {response?.deposit?.coin_type}
                </h3>
              </div>
            </div>
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
                    console.log(parseInt(e.target.value));
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
            <div className="address-area">
              <div className="address-area-info">
                <h3 className="text-white">
                  <i
                    className="fa fa-exclamation-triangle ml-2"
                    aria-hidden="true"
                  ></i>
                </h3>
                <p className="text-white">
                  {t("Only send")} {response?.deposit?.coin_type}{" "}
                  {t("to this address.")}
                  {t(
                    "Sending any other asset to this address may result in the loss of your deposit!"
                  )}
                </p>
              </div>
              <div className="input-url">
                <p id="url-copy" className="address-box">
                  {response.wallet.coin_type == "USDT"
                    ? selectedNetwork?.address
                    : response?.address}
                </p>
                <button
                  type="button"
                  className="btn copy-url-btn"
                  onClick={() => {
                    copyTextById(
                      response.wallet.coin_type == "USDT"
                        ? selectedNetwork?.address
                        : response?.address
                    );
                  }}
                >
                  <i className="fa fa-clone"></i>
                </button>
              </div>
              {!selectedNetwork?.address &&
                response.wallet.coin_type == "USDT" && (
                  <button
                    className=" primary-btn-outline btn-withdraw text-white w-100 mt-2"
                    onClick={() => {
                      GetWalletAddressAction(
                        {
                          wallet_id: id,
                          network_type: selectedNetwork?.network_type ?? "",
                        },
                        setSelectedNetwork
                      );
                    }}
                  >
                    Get address
                  </button>
                )}
              <div className="bar-code-area">
                {response?.address && <Qr value={response?.address} />}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default DepositTab;
