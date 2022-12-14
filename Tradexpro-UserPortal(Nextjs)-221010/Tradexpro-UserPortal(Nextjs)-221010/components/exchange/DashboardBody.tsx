import React from "react";
import { useSelector } from "react-redux";
import { RootState } from "state/store";
import AllSellOrders from "./AllSellOrders";
import AllSellOrdersFull from "./AllSellOrdersFull";
import ExchangeBox from "./ExchangeBox";
import TradesHistory from "./TradesHistory";
import AllBuyOrders from "./AllBuyOrders";
import AllBuyOrdersFull from "./AllBuyOrdersFull";
import dynamic from "next/dynamic";
import OrderHistorySection from "./orderHistorySection";
import useTranslation from "next-translate/useTranslation";
const TradingChart = dynamic(
  () =>
    import("components/exchange/TradingChart").then(
      (mod: any) => mod.TVChartContainer
    ),
  { ssr: false }
);
const DashboardBody = () => {
  const { t } = useTranslation("common");
  const [select, setSelect] = React.useState(3);

  const { dashboard, OpenBookBuy, OpenBooksell, marketTrades, currentPair } =
    useSelector((state: RootState) => state.exchange);
  return (
    <>
      <div className="col-xl-3">
        <div className="trades-section">
          <div>
            <h6 className="text-white">{t("Order Book")}</h6>
          </div>
          <div className="trades-headers mb-3">
            <div className="orderBookIcons">
              <h3
                onClick={() => {
                  setSelect(2);
                }}
                className="icon-center"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                  fill="none"
                  className="css-3kwgah w-25"
                >
                  <path d="M4 4h7v16H4V4z" fill="#0ECB81"></path>
                  <path
                    d="M13 4h7v4h-7V4zm0 6h7v4h-7v-4zm7 6h-7v4h7v-4z"
                    fill="currentColor"
                  ></path>
                </svg>
              </h3>
              <h3
                onClick={() => {
                  setSelect(1);
                }}
                className="icon-center"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                  fill="none"
                  className="css-3kwgah  w-25"
                >
                  <path d="M4 4h7v16H4V4z" fill="#F6465D"></path>
                  <path
                    d="M13 4h7v4h-7V4zm0 6h7v4h-7v-4zm7 6h-7v4h7v-4z"
                    fill="currentColor"
                  ></path>
                </svg>
              </h3>
              <h3
                onClick={() => {
                  setSelect(3);
                }}
                className="icon-center"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                  fill="none"
                  className="css-3kwgah w-25"
                >
                  <path d="M4 4h7v7H4V4z" fill="#F6465D"></path>
                  <path d="M4 13h7v7H4v-7z" fill="#0ECB81"></path>
                  <path
                    d="M13 4h7v4h-7V4zm0 6h7v4h-7v-4zm7 6h-7v4h7v-4z"
                    fill="currentColor"
                  ></path>
                </svg>
              </h3>
            </div>
          </div>
          {select === 1 && (
            <>
              <AllSellOrdersFull OpenBooksell={OpenBooksell} />
              <div className="trades-table-footer">
                <div className="trades-table-row">
                  <span
                    className={
                      parseFloat(
                        dashboard?.last_price_data &&
                          dashboard?.last_price_data[0]?.price
                      ) >
                      parseFloat(
                        dashboard?.last_price_data &&
                          dashboard?.last_price_data[0]?.last_price
                      )
                        ? "value-increase"
                        : parseFloat(
                            dashboard?.last_price_data &&
                              dashboard?.last_price_data[0]?.price
                          ) <
                          parseFloat(
                            dashboard?.last_price_data &&
                              dashboard?.last_price_data[0]?.last_price
                          )
                        ? "value-decrease"
                        : "value-same"
                    }
                  >
                    {parseFloat(
                      dashboard?.last_price_data[0]?.price
                        ? dashboard?.last_price_data[0]?.price
                        : 0
                    )}
                    {parseFloat(
                      dashboard?.last_price_data &&
                        dashboard?.last_price_data[0]?.price
                    ) >
                    parseFloat(
                      dashboard?.last_price_data &&
                        dashboard?.last_price_data[0]?.last_price
                    ) ? (
                      <i className="fa-solid fa-up-long value-increaseicon ml-2"></i>
                    ) : parseFloat(
                        dashboard?.last_price_data &&
                          dashboard?.last_price_data[0]?.price
                      ) <
                      parseFloat(
                        dashboard?.last_price_data &&
                          dashboard?.last_price_data[0]?.last_price
                      ) ? (
                      <i className="fa-solid fa-down-long value-decreaseicon ml-2"></i>
                    ) : (
                      ""
                    )}
                  </span>
                  <span className="value-previous">
                    {" "}
                    {parseFloat(
                      dashboard?.last_price_data[0]?.last_price
                        ? dashboard?.last_price_data[0]?.last_price
                        : 0
                    )}
                    ({dashboard?.order_data?.base_coin})
                  </span>
                </div>
              </div>
            </>
          )}
          {select === 2 && (
            <>
              <div className="trades-table-footer">
                <div className="trades-table-row">
                  <span
                    className={
                      parseFloat(
                        dashboard?.last_price_data &&
                          dashboard?.last_price_data[0]?.price
                      ) >
                      parseFloat(
                        dashboard?.last_price_data &&
                          dashboard?.last_price_data[0]?.last_price
                      )
                        ? "value-increase"
                        : parseFloat(
                            dashboard?.last_price_data &&
                              dashboard?.last_price_data[0]?.price
                          ) <
                          parseFloat(
                            dashboard?.last_price_data &&
                              dashboard?.last_price_data[0]?.last_price
                          )
                        ? "value-decrease"
                        : "value-same"
                    }
                  >
                    {parseFloat(
                      dashboard?.last_price_data[0]?.price
                        ? dashboard?.last_price_data[0]?.price
                        : 0
                    )}
                    {parseFloat(
                      dashboard?.last_price_data &&
                        dashboard?.last_price_data[0]?.price
                    ) >
                    parseFloat(
                      dashboard?.last_price_data &&
                        dashboard?.last_price_data[0]?.last_price
                    ) ? (
                      <i className="fa-solid fa-up-long value-increaseicon ml-2"></i>
                    ) : parseFloat(
                        dashboard?.last_price_data &&
                          dashboard?.last_price_data[0]?.price
                      ) <
                      parseFloat(
                        dashboard?.last_price_data &&
                          dashboard?.last_price_data[0]?.last_price
                      ) ? (
                      <i className="fa-solid fa-down-long value-decreaseicon ml-2"></i>
                    ) : (
                      ""
                    )}
                  </span>
                  <span className="value-previous">
                    {" "}
                    {parseFloat(
                      dashboard?.last_price_data[0]?.last_price
                        ? dashboard?.last_price_data[0]?.last_price
                        : 0
                    )}
                    ({dashboard?.order_data?.base_coin})
                  </span>
                </div>
              </div>
              <AllBuyOrdersFull OpenBookBuy={OpenBookBuy} />
            </>
          )}
          {select === 3 && (
            <div className="tradeSection-both">
              <AllSellOrders OpenBooksell={OpenBooksell} show={18} />
              <div className="trades-table-footer">
                <div className="trades-table-row">
                  <span
                    className={
                      parseFloat(
                        dashboard?.last_price_data &&
                          dashboard?.last_price_data[0]?.price
                      ) >
                      parseFloat(
                        dashboard?.last_price_data &&
                          dashboard?.last_price_data[0]?.last_price
                      )
                        ? "value-increase"
                        : parseFloat(
                            dashboard?.last_price_data &&
                              dashboard?.last_price_data[0]?.price
                          ) <
                          parseFloat(
                            dashboard?.last_price_data &&
                              dashboard?.last_price_data[0]?.last_price
                          )
                        ? "value-decrease"
                        : "value-same"
                    }
                  >
                    {parseFloat(
                      dashboard?.last_price_data
                        ? dashboard?.last_price_data[0]?.price
                        : 0
                    )}
                    {parseFloat(
                      dashboard?.last_price_data &&
                        dashboard?.last_price_data[0]?.price
                    ) >
                    parseFloat(
                      dashboard?.last_price_data &&
                        dashboard?.last_price_data[0]?.last_price
                    ) ? (
                      <i className="fa-solid fa-up-long value-increaseicon ml-2"></i>
                    ) : parseFloat(
                        dashboard?.last_price_data &&
                          dashboard?.last_price_data[0]?.price
                      ) <
                      parseFloat(
                        dashboard?.last_price_data &&
                          dashboard?.last_price_data[0]?.last_price
                      ) ? (
                      <i className="fa-solid fa-down-long value-decreaseicon ml-2"></i>
                    ) : (
                      ""
                    )}
                  </span>
                  <span className="value-previous">
                    {" "}
                    {parseFloat(
                      dashboard?.last_price_data
                        ? dashboard?.last_price_data[0]?.last_price
                        : 0
                    )}
                    ({dashboard?.order_data?.base_coin})
                  </span>
                </div>
              </div>
              <AllBuyOrders OpenBookBuy={OpenBookBuy} show={18} />
            </div>
          )}
        </div>
      </div>
      <div className="col-xl-6">
        <div className="cp-user-buy-coin-content-area">
          <div className="card cp-user-custom-card">
            {currentPair && (
              <TradingChart
                //  @ts-ignore
                coinpair={dashboard?.order_data?.exchange_coin_pair}
              />
            )}
          </div>
        </div>
        <OrderHistorySection />
      </div>
      <div className="col-xl-3">
        <ExchangeBox />
        <TradesHistory marketTrades={marketTrades} />
      </div>
    </>
  );
};

export default DashboardBody;
