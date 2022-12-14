import { formateData } from "common";
import useTranslation from "next-translate/useTranslation";
import React from "react";
import { useSelector } from "react-redux";
import { RootState } from "state/store";
import NotLoggedin from "./notLoggedin";

const TradeOrder = ({ tradeOrder, tradeOrderHistory }: any) => {
  const { t } = useTranslation("common");
  const { dashboard } = useSelector(
    (state: RootState) => state.exchange
  );
  const { isLoggedIn } = useSelector((state: RootState) => state.user);
  return (
    <div
      className={"tab-pane fade" + (tradeOrder ? " show active" : "")}
      id="Funds"
      role="tabpanel"
      aria-labelledby="Funds-tab"
    >
      <div className="table-responsive">
        <table className="table">
          <thead>
            <tr>
              <th scope="col">{t("Transaction id")}</th>
              <th scope="col">
                {" "}
                {t("Fees")}({dashboard?.order_data?.base_coin})
              </th>
              <th scope="col">
                {t("Amount")}({dashboard?.order_data?.trade_coin})
              </th>
              <th scope="col">
                {t("Price")}({dashboard?.order_data?.base_coin})
              </th>
              <th scope="col">
                {" "}
                {t("Processed")}({dashboard?.order_data?.trade_coin})
              </th>
              <th scope="col">{t("Created At")}</th>
            </tr>
          </thead>
          <tbody>
            {tradeOrderHistory?.map((order: any, index: number) => (
              <tr key={index}>
                <td>{parseInt(order.transaction_id)}</td>
                <td>{parseFloat(order.fees).toFixed(8)}</td>
                <td>{order.amount}</td>
                <td>{order.price}</td>
                <td>{order.total}</td>
                <td>{formateData(order.created_at)}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
      {!isLoggedIn && <NotLoggedin />}
    </div>
  );
};

export default TradeOrder;
