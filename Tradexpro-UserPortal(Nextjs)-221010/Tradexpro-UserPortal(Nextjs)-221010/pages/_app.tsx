import "../styles/globals.css";
import type { AppProps } from "next/app";
import { Provider } from "react-redux";
import { store } from "../state/store";
import "react-toastify/dist/ReactToastify.css";
import "slick-carousel/slick/slick.css";
import "slick-carousel/slick/slick-theme.css";
import "/public/style/app.css";
import "/public/style/total.css";
import "/public/style/responsive.css";
import "../styles/nprogress.css";
import Head from "next/head";

import Layout from "layout/index";
import NProgress from "nprogress";
import { useEffect } from "react";
import Router from "next/router";
import useTranslation from "next-translate/useTranslation";
function MyApp({ Component, pageProps }: AppProps) {
  const { t } = useTranslation("common");
  useEffect(() => {
    Router.events.on("routeChangeStart", () => NProgress.start());
    Router.events.on("routeChangeComplete", () => NProgress.done());
    Router.events.on("routeChangeError", () => NProgress.done());
  }, []);
  return (
    <>
      
      <Provider store={store}>
        <Layout>
          <Component {...pageProps} />
        </Layout>
      </Provider>
    </>
  );
}

export default MyApp;
