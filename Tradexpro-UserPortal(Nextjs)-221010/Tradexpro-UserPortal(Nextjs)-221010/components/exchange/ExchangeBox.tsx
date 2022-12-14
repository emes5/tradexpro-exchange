import React, { useEffect, useState } from "react";
import { useSelector } from "react-redux";
import { RootState } from "state/store";
import Limit from "components/exchange/buy/limit";
import Market from "components/exchange/buy/market";
import StopLimit from "components/exchange/buy/stopLimit";
import SellLimit from "components/exchange/sell/limit";
import SellMarket from "components/exchange/sell/market";
import SellStopLimit from "components/exchange/sell/stopLimit";
import useTranslation from "next-translate/useTranslation";

const ExchangeBox = () => {
  type tradingTabType = number;
  const { t } = useTranslation("common");
  const { isLoggedIn } = useSelector((state: RootState) => state.user);
  const { dashboard, currentPair } = useSelector(
    (state: RootState) => state.exchange
  );
  const [tradingTab, setTradingTab] = useState<tradingTabType>(1);
  const [buySellLimitCoinData, setBuySellLimitCoinData] = useState<any>({
    price:
      tradingTab === 1
        ? dashboard?.order_data?.sell_price
        : dashboard?.order_data?.buy_price,
    amount: 0.0,
    total: 0.0,
  });
  const [buySellMarketCoinData, setBuySellMarketCoinData] = useState<any>({
    price:
      tradingTab === 1
        ? dashboard?.order_data?.sell_price
        : dashboard?.order_data?.buy_price,
    amount: 0.0,
    total: 0.0,
  });
  const [buySellStopLimitCoinData, setBuySellStopLimitCoinData] = useState<any>(
    {
      amount: 0.0,
      total: 0,
      limit: 0,
      stop: 0,
    }
  );

  const [buySelectedTab, setBuySelectedTab] = useState<number>(1);
  const [sellSelectedTab, setSellSelectedTab] = useState<number>(1);

  const handletradingTab = (tab: number) => {
    setTradingTab(tab);
  };
  const initialSetUp = () => {
    setBuySellLimitCoinData({
      price:
        tradingTab === 1
          ? dashboard?.order_data?.sell_price
          : dashboard?.order_data?.buy_price,
      amount: 0,
      total: 0,
    });
    setBuySellMarketCoinData({
      price:
        tradingTab === 1
          ? dashboard?.order_data?.sell_price
          : dashboard?.order_data?.buy_price,
      amount: 0,
      total: 0,
    });
    setBuySellStopLimitCoinData({
      amount: 0,
      total: 0,
      limit: 0,
      stop: 0,
    });
  };

  useEffect(() => {
    initialSetUp();
  }, [currentPair, dashboard, tradingTab]);

  return (
    <div className="exchange-box order-box">
      <div className="trades-headers">
        <ul
          id="pills-tab"
          role="tablist"
          className="nav nav-pills transfer-tabs"
        >
          <li
            role="presentation"
            className="nav-item"
            onClick={() => {
              initialSetUp();
              handletradingTab(1);
            }}
          >
            <a
              id="pills-transfer-1-tab"
              data-toggle="pill"
              role="tab"
              aria-controls="pills-transfer-1"
              aria-selected="true"
              className={`nav-link ${tradingTab === 1 ? "active" : ""}`}
            >
              {t("Buy")}
            </a>
          </li>
          <li
            role="presentation"
            className="nav-item"
            onClick={() => {
              initialSetUp();
              handletradingTab(2);
            }}
          >
            <a
              id="pills-transfer-2-tab"
              data-toggle="pill"
              role="tab"
              aria-controls="pills-transfer-2"
              aria-selected="false"
              className={`nav-link ${tradingTab === 2 ? "activeSell" : ""}`}
            >
              {t("Sell")}
            </a>
          </li>
        </ul>
      </div>
      <div id="pills-tabContent" className="tab-content">
        <div
          id="pills-transfer-1"
          role="tabpanel"
          aria-labelledby="pills-transfer-1-tab"
          className={`tab-pane fade show ${tradingTab === 1 && "active"} `}
        >
          <ul
            id="BuyTab"
            role="tablist"
            className="nav nav-tabs inner-tabs-menu"
          >
            <li role="presentation" className="nav-item">
              <a
                id="Limit-tab"
                data-toggle="tab"
                role="tab"
                aria-controls="Limit"
                aria-selected="true"
                className="nav-link active"
                onClick={() => {
                  setBuySelectedTab(1);
                }}
              >
                {t("Limit")}
              </a>
            </li>
            <li role="presentation" className="nav-item">
              <a
                id="Market-tab"
                data-toggle="tab"
                role="tab"
                aria-controls="Market"
                aria-selected="false"
                className="nav-link"
                onClick={() => {
                  setBuySelectedTab(2);
                }}
              >
                {t("Market")}
              </a>
            </li>
            <li role="presentation" className="nav-item">
              <a
                id="Stop-limit-tab"
                data-toggle="tab"
                role="tab"
                aria-controls="Stop-limit"
                aria-selected="false"
                className="nav-link"
                onClick={() => {
                  setBuySelectedTab(3);
                }}
              >
                {t("Stop-limit")}
              </a>
            </li>
          </ul>
          {buySelectedTab === 1 && (
            <Limit
              dashboard={dashboard}
              buySellLimitCoinData={buySellLimitCoinData}
              setBuySellLimitCoinData={setBuySellLimitCoinData}
              isLoggedIn={isLoggedIn}
              currentPair={currentPair}
            />
          )}
          {buySelectedTab === 2 && (
            <Market
              dashboard={dashboard}
              buySellMarketCoinData={buySellMarketCoinData}
              setBuySellMarketCoinData={setBuySellMarketCoinData}
              isLoggedIn={isLoggedIn}
              currentPair={currentPair}
            />
          )}
          {buySelectedTab === 3 && (
            <StopLimit
              dashboard={dashboard}
              buySellStopLimitCoinData={buySellStopLimitCoinData}
              setBuySellStopLimitCoinData={setBuySellStopLimitCoinData}
              isLoggedIn={isLoggedIn}
              currentPair={currentPair}
            />
          )}
        </div>
        <div
          id="pills-transfer-2"
          role="tabpanel"
          aria-labelledby="pills-transfer-2-tab"
          className={`tab-pane fade show ${tradingTab === 2 && "active"} `}
        >
          <ul
            id="SellTab"
            role="tablist"
            className="nav nav-tabs inner-tabs-menu"
          >
            <li
              role="presentation"
              className="nav-item sellBox"
              onClick={() => {
                setSellSelectedTab(1);
              }}
            >
              <a
                id="sell-Limit-tab"
                data-toggle="tab"
                role="tab"
                aria-controls="LimitSell"
                aria-selected="true"
                className="nav-link active"
              >
                {t("Limit")}
              </a>
            </li>
            <li
              role="presentation"
              className="nav-item sellBox"
              onClick={() => {
                setSellSelectedTab(2);
              }}
            >
              <a
                id="sell-Market-tab"
                data-toggle="tab"
                role="tab"
                aria-controls="MarketSell"
                aria-selected="false"
                className="nav-link"
              >
                {t("Market")}
              </a>
            </li>
            <li
              role="presentation"
              className="nav-item sellBox"
              onClick={() => {
                setSellSelectedTab(3);
              }}
            >
              <a
                id="sell-Stop-limit-tab"
                data-toggle="tab"
                role="tab"
                aria-controls="Stop-limitSell"
                aria-selected="false"
                className="nav-link"
              >
                {t("Stop-limit")}
              </a>
            </li>
          </ul>
          {sellSelectedTab === 1 && (
            <SellLimit
              dashboard={dashboard}
              buySellLimitCoinData={buySellLimitCoinData}
              setBuySellLimitCoinData={setBuySellLimitCoinData}
              isLoggedIn={isLoggedIn}
              currentPair={currentPair}
            />
          )}
          {sellSelectedTab === 2 && (
            <SellMarket
              dashboard={dashboard}
              buySellMarketCoinData={buySellMarketCoinData}
              setBuySellMarketCoinData={setBuySellMarketCoinData}
              isLoggedIn={isLoggedIn}
              currentPair={currentPair}
            />
          )}
          {sellSelectedTab === 3 && (
            <SellStopLimit
              dashboard={dashboard}
              buySellStopLimitCoinData={buySellStopLimitCoinData}
              setBuySellStopLimitCoinData={setBuySellStopLimitCoinData}
              isLoggedIn={isLoggedIn}
              currentPair={currentPair}
            />
          )}
        </div>
      </div>
    </div>
  );
};

export default ExchangeBox;
