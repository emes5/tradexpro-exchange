import Navbar from "components/common/navbar";
import { useRouter } from "next/router";
import { ToastContainer } from "react-toastify";
import { commomSettings } from "service/landing-page";
import { useEffect, useState } from "react";
import { GetUserInfoByTokenAction } from "state/actions/user";
import { useDispatch, useSelector } from "react-redux";
import Cookies from "js-cookie";
import { RootState } from "state/store";
import useTranslation from "next-translate/useTranslation";
import CookieAccept from "components/common/cookie-accept";
import Head from "next/head";
import { setLoading, setLogo } from "state/reducer/user";
import { setSettings } from "state/reducer/common";
import Loading from "components/common/loading";
const Index = ({ children }: any) => {
  const [navbarVisible, setNavbarVisible] = useState(false);
  const [showterms, setShowTerms] = useState(false);
  const [metaData, setMetaData] = useState({
    app_title: "",
    copyright_text: "",
    exchange_url: "",
    favicon: "",
    login_logo: "",
    logo: "",
    maintenance_mode: "no",
  });
  const { isLoading, isLoggedIn } = useSelector(
    (state: RootState) => state.user
  );
  const { settings } = useSelector((state: RootState) => state.common);
  const { t } = useTranslation("common");
  const dispatch = useDispatch();
  const router = useRouter();
  const getCommonSettings = async () => {
    dispatch(setLoading(true))
    const response = await commomSettings();
    dispatch(setLogo(response.data.logo));
    dispatch(setSettings(response.data));
    setMetaData(response.data);
    dispatch(setLoading(false))
  };
  useEffect(() => {
    getCommonSettings();
  }, []);
  useEffect(() => {
    const path = router.pathname;
    if (
      path === "/authentication/signup" ||
      path === "/authentication/signin" ||
      path === "/exchange/dashboard" ||
      path === "/authentication/forgot-password" ||
      path === "/authentication/reset-password" ||
      path === "/authentication/g2f-verify" ||
      path === "/" ||
      path === "/authentication/verify-email" ||
      path === "user/notification"
    ) {
      setNavbarVisible(false);
    } else {
      setNavbarVisible(true);
    }
  }, [router.pathname]);
  const iUnderStand = () => {
    Cookies.set("terms", "yes");
    setShowTerms(false);
  };
  useEffect(() => {
    const token = Cookies.get("token");
    const terms = Cookies.get("terms");
    if (terms === "yes" && settings.cookie_status == "0") {
      setShowTerms(false);
    } else if (terms != "yes" && settings.cookie_status == "1") {
      setShowTerms(true);
    }
    if (token) {
      dispatch(GetUserInfoByTokenAction());
    }
  }, [isLoggedIn, settings.cookie_status]);
  return navbarVisible ? (
    <div>
      {isLoading && <Loading />}
      <Head>
        <title>{metaData?.app_title || process.env.NEXT_PUBLIC_APP_NAME}</title>
        <link
          rel="shortcut icon"
          href={metaData?.favicon || process.env.NEXT_PUBLIC_FAVICON}
        />
      </Head>
      <Navbar />
      <ToastContainer
        position="top-right"
        autoClose={5000}
        hideProgressBar={false}
        newestOnTop={false}
        closeOnClick
        rtl={false}
        pauseOnFocusLoss
        draggable
        pauseOnHover
      />
      <div className="cp-user-main-wrapper">{children}</div>
      {showterms && <CookieAccept iUnderStand={iUnderStand} />}
    </div>
  ) : (
    <>
      <Head>
        <title>{metaData?.app_title || process.env.NEXT_PUBLIC_APP_NAME}</title>
        <link
          rel="shortcut icon"
          href={metaData?.favicon || process.env.NEXT_PUBLIC_FAVICON}
        />
      </Head>
      <ToastContainer
        position="top-right"
        autoClose={5000}
        hideProgressBar={false}
        newestOnTop={false}
        closeOnClick
        rtl={false}
        pauseOnFocusLoss
        draggable
        pauseOnHover
      />
      <div>{children}</div>
      {showterms && <CookieAccept iUnderStand={iUnderStand} />}
    </>
  );
};

export default Index;
