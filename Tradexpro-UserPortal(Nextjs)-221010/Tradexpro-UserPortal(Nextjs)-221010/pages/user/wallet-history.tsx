import type { GetServerSideProps, NextPage } from "next";
import React, { useState } from "react";
import ReportSidebar from "layout/report-sidebar";
import DataTable from "react-data-table-component";
import {
  WithdrawAndDepositHistoryAction,
  handleSearch,
} from "state/actions/reports";
import { SSRAuthCheck } from "middlewares/ssr-authentication-check";
import Loading from "components/common/TableLoading";
import { useRouter } from "next/router";
import useTranslation from "next-translate/useTranslation";
import moment from "moment";
const DepositHistory: NextPage = () => {
  const router = useRouter();
  const { type } = router.query;
  const { t } = useTranslation("common");
  const [search, setSearch] = useState<any>("");
  const [processing, setProcessing] = useState<boolean>(false);
  const [history, setHistory] = useState<any>([]);
  const [stillHistory, setStillHistory] = useState<any>([]);
  const LinkTopaginationString = (page: any) => {
    const url = page.url.split("?")[1];
    const number = url.split("=")[1];
    WithdrawAndDepositHistoryAction(
      type as string,
      10,
      parseInt(number),
      setHistory,
      setProcessing,
      setStillHistory
    );
  };
  const getReport = async () => {
    if (type === "deposit" || type === "withdrawal") {
      WithdrawAndDepositHistoryAction(
        type as string,
        10,
        1,
        setHistory,
        setProcessing,
        setStillHistory
      );
    }
  };
  const columns = [
    {
      name: t("Created At"),
      selector: (row: any) => row.created_at,
      sortable: true,
      cell: (row: any) => (
        <div>{moment(row.created_at).format("YYYY-MM-DD HH:mm:ss")}</div>
      ),
    },
    {
      name: t("Address"),
      selector: (row: any) => row.address,
      sortable: true,
    },
    {
      name: t("Coin Type"),
      selector: (row: any) => row.coin_type,
      sortable: true,
    },
    {
      name: t("Amount"),
      selector: (row: any) => row.amount,
      sortable: true,
    },
    {
      name: t("Fees"),
      selector: (row: any) => row.fees,
      sortable: true,
    },
    {
      name: type === t("deposit") ? t("Transaction Id") : t("Transaction Hash"),
      selector: (row: any) =>
        type === "deposit" ? row.transaction_id : row.transaction_hash,
      sortable: true,
    },
    {
      name: t("Status"),
      selector: (row: any) => row.status,
      sortable: true,
      cell: (row: any) => (
        <div>
          {row.status == 0 ? (
            <span className="text-warning">{t("Pending")}</span>
          ) : row.status == 1 ? (
            <span className="text-success"> {t("Success")}</span>
          ) : (
            <span className="text-danger">{t("Failed")}</span>
          )}
        </div>
      ),
    },
  ];
  React.useEffect(() => {
    getReport();
    return () => {
      setHistory([]);
    };
  }, [type]);
  return (
    <div className="page-wrap">
      <ReportSidebar />
      <div className="page-main-content">
        <div className="container-fluid">
          <div className="section-top-wrap mb-25">
            <div className="overview-area">
              <div className="overview-left">
                <h2 className="section-top-title">
                  {type === "deposit"
                    ? t("Deposit History")
                    : t("Withdrawal History")}
                </h2>
              </div>
            </div>
          </div>

          <div className="asset-balances-area">
            {processing ? (
              <Loading />
            ) : (
              <div className="asset-balances-left">
                <div className="section-wrapper">
                  <div className="table-responsive">
                    <div
                      id="assetBalances_wrapper"
                      className="dataTables_wrapper no-footer"
                    >
                      <div className="dataTables_head">
                        <div
                          className="dataTables_length"
                          id="assetBalances_length"
                        >
                          <label className="">
                            {t("Show")}
                            <select
                              name="assetBalances_length"
                              aria-controls="assetBalances"
                              className=""
                              onChange={(e) => {
                                WithdrawAndDepositHistoryAction(
                                  type as string,
                                  parseInt(e.target.value),
                                  1,
                                  setHistory,
                                  setProcessing,
                                  setStillHistory
                                );
                              }}
                            >
                              <option value="10">10</option>
                              <option value="25">25</option>
                              <option value="50">50</option>
                              <option value="100">100</option>
                            </select>
                            {t("entries")}
                          </label>
                        </div>
                        <div id="table_filter" className="dataTables_filter">
                          <label>
                            {t("Search:")}
                            <input
                              type="search"
                              name="search"
                              className="data_table_input"
                              placeholder=""
                              value={search}
                              aria-controls="table"
                              onChange={(e) => {
                                handleSearch(
                                  e,
                                  setSearch,
                                  stillHistory,
                                  setHistory
                                );
                              }}
                            />
                          </label>
                        </div>
                      </div>
                    </div>
                    <div className="cp-user-wallet-table table-responsive">
                      <DataTable columns={columns} data={history} />
                    </div>
                    {history.length > 0 && (
                      <div
                        className="pagination-wrapper"
                        id="assetBalances_paginate"
                      >
                        <span>
                          {stillHistory?.histories?.links.map(
                            (link: any, index: number) =>
                              link.label === "&laquo; Previous" ? (
                                <a
                                  className="paginate-button"
                                  onClick={() => {
                                    if (link.url) LinkTopaginationString(link);
                                  }}
                                  key={index}
                                >
                                  <i className="fa fa-angle-left"></i>
                                </a>
                              ) : link.label === "Next &raquo;" ? (
                                <a
                                  className="paginate-button"
                                  onClick={() => LinkTopaginationString(link)}
                                  key={index}
                                >
                                  <i className="fa fa-angle-right"></i>
                                </a>
                              ) : (
                                <a
                                  className="paginate_button paginate-number"
                                  aria-controls="assetBalances"
                                  data-dt-idx="1"
                                  onClick={() => LinkTopaginationString(link)}
                                  key={index}
                                >
                                  {link.label}
                                </a>
                              )
                          )}
                        </span>
                      </div>
                    )}
                  </div>
                </div>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};
export const getServerSideProps: GetServerSideProps = async (ctx: any) => {
  await SSRAuthCheck(ctx, "/user/wallet-history");
  return {
    props: {},
  };
};

export default DepositHistory;
