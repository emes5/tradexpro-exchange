import React, { useEffect } from "react";
import { useDispatch, useSelector } from "react-redux";
import Tooltip from "rc-tooltip";
import "rc-tooltip/assets/bootstrap.css";
import { RootState } from "state/store";
import { setCurrentPair } from "state/reducer/exchange";
import { useRouter } from "next/router";
import useTranslation from "next-translate/useTranslation";
import DataTable from "react-data-table-component";
const SelectCurrency = () => {
  const router = useRouter();
  const [pairs, setPairs] = React.useState([]);
  const { t } = useTranslation("common");
  const { dashboard } = useSelector((state: RootState) => state.exchange);
  const customStyles = {
    rows: {
      style: {
        backgroundColor: "#151515",
        color: "#fff",
        borderColor: "#151515",
      },
    },
    headCells: {
      style: {
        backgroundColor: "#151515",
        color: "#fff",
        borderColor: "#151515",
      },
    },
    cells: {
      style: {
        width: "100%",
        backgroundColor: "#151515",
        color: "#fff",
        borderColor: "#151515",
        fontSize: "10px",
      },
    },
  };
  const columns = [
    {
      name: t("Coin"),
      selector: (row: any) => row.coin,
      sortable: true,
      cell: (row: any) => {
        return (
          <Tooltip
            placement={"left"}
            overlay={
              <span>
                <span>
                  {t("Last Price:")} {row.last_price}
                </span>
              </span>
            }
            trigger={["hover"]}
            overlayClassName="rcTooltipOverlay"
          >
            <div
              onClick={async () => {
                await localStorage.setItem("base_coin_id", row?.parent_coin_id);
                await localStorage.setItem("trade_coin_id", row?.child_coin_id);
                await localStorage.setItem("current_pair", row.coin_pair);

                await dispatch(setCurrentPair(row.coin_pair));

                router.reload();
              }}
            >
              <span className="coin-name">{row?.coin_pair_name}</span>
            </div>
          </Tooltip>
        );
      },
    },
    {
      name: t("Last"),
      selector: (row: any) => row.price,
      sortable: true,
      cell: (row: any) => {
        return (
          <Tooltip
            placement={"left"}
            overlay={
              <span>
                <span>
                  {t("Last Price:")} {row.last_price}
                </span>
              </span>
            }
            trigger={["hover"]}
            overlayClassName="rcTooltipOverlay"
          >
            <span className="text-center w-40 text-white">
              {parseFloat(row.last_price).toFixed(4)}
            </span>
          </Tooltip>
        );
      },
    },
    {
      name: t("Change"),

      selector: (row: any) => row.price_change,
      sortable: true,
      cell: (row: any) => {
        return (
          <Tooltip
            placement={"left"}
            overlay={
              <span>
                <span>
                  {t("Last Price:")} {row.last_price}
                </span>
              </span>
            }
            trigger={["hover"]}
            overlayClassName="rcTooltipOverlay"
          >
            <span
              className={
                parseFloat(row?.price_change) >= 0
                  ? "text-success"
                  : "text-danger"
              }
            >
              {parseFloat(row.price_change).toFixed(2)}%
            </span>
          </Tooltip>
        );
      },
    },
  ];
  const dispatch = useDispatch();
  useEffect(() => {
    if (dashboard?.pairs) {
      setPairs(dashboard.pairs);
    }
  }, [dashboard]);
  return (
    <div
      className="cp-user-buy-coin-content-area dropdown-menu"
      aria-labelledby="dropdownMenuButton"
    >
      <div className="cp-user-wallet-table dashboard-coin_pairs table-responsive">
        <div
          id="exchangeCoinPair_wrapper"
          className="dataTables_wrapper no-footer"
        >
          <div id="exchangeCoinPair_filter" className="dataTables_filter">
            <label>
              <input
                type="search"
                className=""
                placeholder={t("Search")}
                aria-controls="exchangeCoinPair"
                onChange={(e) => {
                  // on typing end

                  const searchText = e.target.value;
                  const filteredPairs = dashboard.pairs.filter((pair: any) => {
                    return pair?.coin_pair_name
                      .toLowerCase()
                      .includes(searchText.toLowerCase());
                  });
                  setPairs(filteredPairs);
                }}
              />
            </label>
          </div>
          <div
            id="exchangeCoinPair_processing"
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
                  width: "415px",
                  paddingRight: "17px",
                }}
              ></div>
            </div>
            <div
              className="dataTables_scrollBody"
              style={{
                position: "relative",
                overflow: "auto",
                height: "448px",
                width: "100%",
              }}
            >
              <DataTable
                columns={columns}
                data={pairs}
                customStyles={customStyles}
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default SelectCurrency;
