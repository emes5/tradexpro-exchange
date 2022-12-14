import useTranslation from "next-translate/useTranslation";
import Link from "next/link";
import React from "react";
import { useSelector } from "react-redux";
import { RootState } from "state/store";

const Footer = ({ customPageData, socialData, copyright_text }: any) => {
  const { t } = useTranslation("common");
  const { settings } = useSelector((state: RootState) => state.common);

  return (
    <footer className="footer-area pt--70">
      <div className="footer-top">
        <div className="container">
          <div className="row">
            <div className="col-lg-3 col-md-6 col-sm-6 mb-30">
              <div className="single-wedgets text-widget">
                <div className="widget-title">
                  <h4>{t("Products")}</h4>
                </div>
                <div className="widget-inner">
                  <ul>
                    {customPageData?.map(
                      (item: any) =>
                        item.type === 1 && (
                          <li>
                            <Link href={"/page-details/" + item.key}>
                              {item.title}
                            </Link>
                          </li>
                        )
                    )}
                  </ul>
                </div>
              </div>
            </div>
            <div className="col-lg-3 col-md-6 col-sm-6 mb-30">
              <div className="single-wedgets text-widget">
                <div className="widget-title">
                  <h4>{t("Service")}</h4>
                </div>
                <div className="widget-inner">
                  <ul>
                    {customPageData?.map(
                      (item: any) =>
                        item.type === 2 && (
                          <li>
                            <Link href={"/page-details/" + item.key}>
                              {item.title}
                            </Link>
                          </li>
                        )
                    )}
                  </ul>
                </div>
              </div>
            </div>
            <div className="col-lg-3 col-md-6 col-sm-6 mb-30">
              <div className="single-wedgets text-widget">
                <div className="widget-title">
                  <h4>{t("Support")}</h4>
                </div>
                <div className="widget-inner">
                  <ul>
                    {customPageData?.map(
                      (item: any) =>
                        item.type === 3 && (
                          <li>
                            <Link href={"/page-details/" + item.key}>
                              {item.title}
                            </Link>
                          </li>
                        )
                    )}
                  </ul>
                </div>
              </div>
            </div>
            <div className="col-lg-2 col-md-6 col-sm-6">
              <div className="single-wedgets social-link">
                <div className="widget-title">
                  <h4>{t("Community")}</h4>
                </div>
                <div className="widget-inner">
                  <ul>
                    {socialData?.map((social: any, index: any) => (
                      <li key={index}>
                        <a href={social.media_link}>
                          <img
                            src={social.media_icon}
                            alt={social.media_title}
                            height={20}
                            width={20}
                          />
                        </a>
                      </li>
                    ))}
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div className="footer-bottom">
        <div className="container">
          <div className="footer-bottom-wrap">
            <div className="row align-items-center">
              <div className="col-md-12">
                <div className="copyright-area text-center text-md-center">
                  <p>
                    {copyright_text || t("Copyright@2022")}{" "}
                    <a href="">{settings.app_title ?? t("TradexPro")}</a>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
