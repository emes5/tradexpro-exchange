import React from "react";
import Tooltip from "rc-tooltip";
import "rc-tooltip/assets/bootstrap.css";
import { useDispatch } from "react-redux";
import { setBuyPrice } from "state/reducer/exchange";
import useTranslation from "next-translate/useTranslation";
const AllBuyOrders = ({ OpenBookBuy }: any) => {
  const { t } = useTranslation("common");
  const dispatch = useDispatch();
  const changeSellPrice = (price: number) => {
    dispatch(setBuyPrice(price));
  };
  const [summary, setSummary] = React.useState({
    amount: 0,
    total: 0,
  });
  return (
    <div className="sell-order">
      <div className="trades-table">
        <div className="trades-table-body" />
        <div
          id="exchangeAllSellOrders_wrapper"
          className="dataTables_wrapper no-footer"
        >
          <div
            id="exchangeAllSellOrders_processing"
            className="dataTables_processing"
            style={{ display: "none" }}
          >
            {t("Processing...")}
          </div>
          <div className="dataTables_scroll">
            <div
              className="dataTables_scrollHead"
              style={{
                overflow: "hidden",
                position: "relative",
                border: "0px",
                width: "100%",
              }}
            >
              <div
                className="dataTables_scrollHeadInner"
                style={{
                  boxSizing: "content-box",
                  width: "431.25px",
                  paddingRight: "0px",
                }}
              >
                <table
                  className="table dataTable no-footer"
                  role="grid"
                  style={{
                    marginLeft: "0px",
                    width: "431.25px",
                  }}
                >
                  <thead>
                    <tr role="row"></tr>
                    <tr role="row"></tr>
                    <tr role="row"></tr>
                  </thead>
                </table>
              </div>
            </div>
            <div
              className="dataTables_scrollBody"
              style={{
                position: "relative",
                overflow: "auto",
                height: "855px !important",
                width: "100%",
              }}
            >
              <table
                id="exchangeAllSellOrders"
                className="table dataTable no-footer"
                role="grid"
                style={{ width: "100%" }}
              >
                <thead>
                  <tr role="row">
                    <th
                      className="table-col price sorting_disabled"
                      rowSpan={1}
                      colSpan={1}
                      style={{ width: "170.656px" }}
                      aria-label="Price"
                    ></th>
                    <th
                      className="table-col amount sorting_disabled"
                      rowSpan={1}
                      colSpan={1}
                      style={{ width: "120.75px" }}
                      aria-label="Amount"
                    ></th>
                    <th
                      className="table-col time text-right sorting_desc"
                      rowSpan={1}
                      colSpan={1}
                      style={{ width: "79.8438px" }}
                      aria-label="Time"
                    ></th>
                  </tr>
                </thead>
                <tbody>
                  {OpenBookBuy?.length !== 0 ? (
                    OpenBookBuy?.map((item: any, index: number) => (
                      <Tooltip
                        key={index}
                        placement={"right"}
                        overlay={
                          <span>
                            <span>
                              {t("Avg Price:")} {item.price}
                            </span>
                            <br />
                            <span>
                              {t("Amount:")} {summary.amount}
                            </span>
                            <br />

                            <span>
                              {t("Size:")} {summary.total}
                            </span>
                          </span>
                        }
                        trigger={["hover"]}
                        overlayClassName="rcTooltipOverlay"
                      >
                        <tr
                          className="odd"
                          onClick={() => {
                            changeSellPrice(item.price);
                          }}
                          onMouseEnter={() => {
                            const selectedIndex = index;
                            const firstIndex = 0;
                            let sumtotal = 0;
                            let sumAmount = 0;
                            for (let i = selectedIndex; i >= firstIndex; i--) {
                              sumtotal += parseFloat(OpenBookBuy[i].total);
                              sumAmount += parseFloat(OpenBookBuy[i].amount);
                            }
                            setSummary({
                              amount: sumAmount,
                              total: sumtotal,
                            });
                          }}
                        >
                          <td>
                            <div className="asset">
                              <span className="text-success">
                                {parseFloat(item.price)}
                              </span>
                            </div>
                          </td>
                          <td>
                            <div className="asset">
                              <span className="asset-name">{item.amount}</span>
                            </div>
                          </td>
                          <td>
                            <div className="asset">
                              <span className="asset-name">
                                {parseFloat(item.total).toFixed(2)}
                              </span>
                            </div>
                          </td>
                        </tr>
                      </Tooltip>
                    ))
                  ) : (
                    <tr>
                      <td className="">{t("No data available in table")}</td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default AllBuyOrders;
