import useTranslation from "next-translate/useTranslation";
import Link from "next/link";
import React from "react";

const SwapCoinSidebar = () => {
  const { t } = useTranslation("common");
  return (
    <div className="page-left-sidebar">
      <div className="sidebar-top">
        <ul className="left-menu">
          <Link href="">
            <li className="active ">
              <a href="">{t("Market")}</a>
            </li>
          </Link>
          <Link href="">
            <li className=" active ">
              <a href="">{t("Order")}</a>
            </li>
          </Link>
        </ul>
      </div>
    </div>
  );
};

export default SwapCoinSidebar;
