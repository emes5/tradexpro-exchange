import { formateData } from "common";
import useTranslation from "next-translate/useTranslation";
import React from "react";
import { useSelector } from "react-redux";
import { RootState } from "state/store";
import NotLoggedin from "./notLoggedin";
type Props = {
  orderHistory: boolean;
};

const OrderHistory = ({
  orderHistory,
  sellOrderHistoryState,
  buyOrderHistoryState,
}: any) => {
  const { t } = useTranslation("common");
  const { dashboard } = useSelector((state: RootState) => state.exchange);
  const { isLoggedIn } = useSelector((state: RootState) => state.user);
  return (
    <div
      className={"tab-pane fade" + (orderHistory ? " show active" : "")}
      id="Trade-history"
      role="tabpanel"
      aria-labelledby="Trade-history-tab"
    >
      <div className="buy-sell-tabs">
        <ul className="nav nav-tabs" id="buySellTab" role="tablist">
          <li className="nav-item" role="presentation">
            <a
              className="nav-link active"
              id="buy-tab"
              data-toggle="tab"
              href="#buy"
              role="tab"
              aria-controls="buy"
              aria-selected="true"
            >
              {t("buy")}
            </a>
          </li>
          <li className="nav-item" role="presentation">
            <a
              className="nav-link"
              id="sell-tab"
              data-toggle="tab"
              href="#sell"
              role="tab"
              aria-controls="sell"
              aria-selected="false"
            >
              {t("sell")}
            </a>
          </li>
        </ul>
        <div className="tab-content" id="buySellTabContent">
          <div
            className="tab-pane fade show active"
            id="buy"
            role="tabpanel"
            aria-labelledby="buy-tab"
          >
            <div className="table-responsive">
              <table className="table">
                <thead>
                  <tr>
                    <th scope="col">{t("Type")}</th>
                    <th scope="col">{t("Pair")}</th>
                    <th scope="col">
                      {t("Price")}({dashboard?.order_data?.base_coin})
                    </th>
                    <th scope="col">
                      {t("Amount")}({dashboard?.order_data?.trade_coin})
                    </th>
                    <th scope="col">
                      {t("Fees")}({dashboard?.order_data?.base_coin})
                    </th>
                    <th scope="col">
                      {t("total")}({dashboard?.order_data?.base_coin})
                    </th>
                    <th scope="col">{t("Created At")}</th>
                  </tr>
                </thead>
                <tbody>
                  {buyOrderHistoryState?.map((item: any, index: number) => (
                    <tr key={index}>
                      <td
                        className={
                          item?.type === "sell" ? "text-danger" : "text-success"
                        }
                      >
                        {item?.type}
                      </td>
                      <td>
                        {dashboard.order_data.exchange_coin_pair &&
                          dashboard.order_data.exchange_coin_pair}
                      </td>
                      <td>{item.price}</td>
                      <td>{item.amount}</td>
                      <td>{item.fees}</td>
                      <td>{item.total}</td>
                      <td>{formateData(item.created_at)}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
              {!isLoggedIn && <NotLoggedin />}
            </div>
          </div>
          <div
            className="tab-pane fade"
            id="sell"
            role="tabpanel"
            aria-labelledby="sell-tab"
          >
            <div className="table-responsive">
              <table className="table">
                <thead>
                  <tr>
                    <th scope="col">{t("Type")}</th>
                    <th scope="col">{t("Pair")}</th>
                    <th scope="col">
                      {t("Price")}({dashboard?.order_data?.base_coin})
                    </th>
                    <th scope="col">
                      {t("Amount")}({dashboard?.order_data?.trade_coin})
                    </th>
                    <th scope="col">
                      {t("Fees")}({dashboard?.order_data?.base_coin})
                    </th>
                    <th scope="col">
                      {t("total")}({dashboard?.order_data?.base_coin})
                    </th>
                    <th scope="col">{t("Created At")}</th>
                  </tr>
                </thead>
                <tbody>
                  {sellOrderHistoryState?.map((item: any, index: number) => (
                    <tr key={index}>
                      <td
                        className={
                          item?.type === "sell" ? "text-danger" : "text-success"
                        }
                      >
                        {item?.type}
                      </td>
                      <td>
                        {dashboard.order_data.exchange_coin_pair &&
                          dashboard.order_data.exchange_coin_pair}
                      </td>
                      <td>{item.price}</td>
                      <td>{item.amount}</td>
                      <td>{item.fees}</td>
                      <td>{item.total}</td>
                      <td>{formateData(item.created_at)}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
              {!isLoggedIn && <NotLoggedin />}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default OrderHistory;
