import useTranslation from "next-translate/useTranslation";
import Link from "next/link";
import React from "react";
import { useSelector } from "react-redux";
import { RootState } from "state/store";

const CookieAccept = ({ iUnderStand }: any) => {
  const { t } = useTranslation("common");
  const { settings } = useSelector((state: RootState) => state.common);
  return (
    <div className="wrapper">
      <img src={settings.cookie_image || "/undraw_warning_re_eoyh.svg"} />
      <div className="content">
        <header>{settings.cookie_header || t("Cookies Constent")}</header>
        <p>
          {settings.cookie_text ||
            t("This website use cookies to ensure you get the best experience")}
        </p>
        <div className="buttons">
          <button
            className="item"
            onClick={() => {
              iUnderStand();
            }}
          >
            {settings.cookie_button_text || t("I understand")}
          </button>
          <Link
            href={
              settings.cookie_page_key
                ? `/page-details/${settings.cookie_page_key}`
                : "/page-details/privacy"
            }
          >
            <a href="#" className="item">
              {settings.cookie_page_key || t("privacy policy")}
            </a>
          </Link>
        </div>
      </div>
    </div>
  );
};

export default CookieAccept;
