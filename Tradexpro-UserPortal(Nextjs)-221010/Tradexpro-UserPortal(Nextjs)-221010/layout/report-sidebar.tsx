import Link from "next/link";
import React from "react";
import { useRouter } from "next/router";
import useTranslation from "next-translate/useTranslation";
const ReportSidebar = () => {
  const router = useRouter();
  const { t } = useTranslation("common");
  return (
    <div className="page-left-sidebar">
      <div className="sidebar-top">
        <ul className="left-menu">
          <Link href="/user/wallet-history?type=deposit">
            <li className={router.query.type == "deposit" ? "active" : ""}>
              <a>{t("Deposit History")}</a>
            </li>
          </Link>
          <Link href="/user/wallet-history?type=withdrawal">
            <li className={router.query.type == "withdrawal" ? "active" : ""}>
              <a href="coinSwapHistory">{t("Withdrawal History")}</a>
            </li>
          </Link>
          <Link href="/user/swap-history">
            <li
              className={
                router.pathname == "/user/swap-history" ? "active" : ""
              }
            >
              <a href="coinSwapHistory">{t("Swap History")}</a>
            </li>
          </Link>
          <Link href="/user/buy-order-history">
            <li
              className={
                router.pathname == "/user/buy-order-history" ? "active" : ""
              }
            >
              <a href="getAllOrdersHistoryBuy">{t("Buy Order History")}</a>
            </li>
          </Link>
          <Link href="/user/sell-order-history">
            <li
              className={
                router.pathname == "/user/sell-order-history" ? "active" : ""
              }
            >
              <a href="getAllOrdersHistorySell">{t("Sell Order History")}</a>
            </li>
          </Link>
          <Link href="/user/transaction-history">
            <li
              className={
                router.pathname == "/user/transaction-history" ? "active" : ""
              }
            >
              <a href="getAllTransactionHistory">{t("Transaction History")}</a>
            </li>
          </Link>{" "}
          <Link href="/user/currency-deposit-history">
            <li
              className={
                router.pathname == "/user/currency-deposit-history"
                  ? "active"
                  : ""
              }
            >
              <a href="getAllTransactionHistory">{t("Fiat deposit History")}</a>
            </li>
          </Link>
        </ul>
      </div>
    </div>
  );
};

export default ReportSidebar;
