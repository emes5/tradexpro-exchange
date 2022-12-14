import type { NextPage } from "next";
import DashboardNavbar from "components/common/dashboardNavbar";
import { useEffect, useState } from "react";
import Echo from "laravel-echo";
import Pusher from "pusher-js";
import Loading from "components/common/loading";
import SelectCurrency from "components/exchange/selectCurrency";
import CurrencyLevel from "components/exchange/currencyLevel";
import DashboardBody from "components/exchange/DashboardBody";
import { useSelector, useDispatch } from "react-redux";
import { RootState } from "state/store";
import {
  initialDashboardCallAction,
  initialDashboardCallActionWithToken,
} from "state/actions/exchange";
import {
  setCurrentPair,
  setOpenBookBuy,
  setOpenBooksell,
} from "state/reducer/exchange";
import useTranslation from "next-translate/useTranslation";
let socketCall = 0;
async function listenMessages(dispatch: any) {
  //@ts-ignore
  window.Pusher = Pusher;
  //@ts-ignore
  window.Echo = new Echo({
    broadcaster: "pusher",
    key: "test",
    wsHost: process.env.NEXT_PUBLIC_HOST_SOCKET,
    wsPort: 6006,
    wssPort: 443,
    cluster: "mt1",
    disableStats: true,
    enabledTransports: ["ws", "wss"],
  });
  //@ts-ignore
  window.Echo.channel("dashboard").listen(".order_place", (e) => {
    if (e.order_type === "buy") dispatch(setOpenBookBuy(e.orders));
    if (e.order_type === "sell") dispatch(setOpenBooksell(e.orders));
  });
  //@ts-ignore
  window.Echo.channel(
    `trade-info-${localStorage.getItem("base_coin_id")}-${localStorage.getItem(
      "trade_coin_id"
    )}`
  ).listen(".process", (e: any) => {});
}
const Dashboard: NextPage = () => {
  const { t } = useTranslation("common");
  const dispatch = useDispatch();
  const [isLoading, setisLoading] = useState(true);
  const { isLoggedIn } = useSelector((state: RootState) => state.user);
  const { dashboard, currentPair } = useSelector(
    (state: RootState) => state.exchange
  );

  useEffect(() => {
    const pair = localStorage.getItem("current_pair");
    if (pair) {
      dispatch(setCurrentPair(pair));
      dispatch(initialDashboardCallAction(pair, dashboard, setisLoading));
    } else {
      dispatch(
        initialDashboardCallAction(currentPair, dashboard, setisLoading)
      );
    }
  }, [isLoggedIn, currentPair]);
  useEffect(() => {
    if (
      dashboard?.order_data?.base_coin_id &&
      dashboard?.order_data?.trade_coin_id
    ) {
      dispatch(
        initialDashboardCallActionWithToken(
          currentPair,
          dashboard,
          setisLoading
        )
      );
    }
  }, [dashboard?.order_data?.base_coin_id]);
  useEffect(() => {
    if (socketCall === 0) {
      listenMessages(dispatch);
    }
    socketCall = 1;
  });
  return (
    <div className="container-dashboard">
      <div className="background-col">
        <DashboardNavbar />
        {isLoading && <Loading />}
        <div className="mt-5"></div>
        <div className="cp-user-sidebar-area">
          <div
            className="scroll-wrapper cp-user-sidebar-menu scrollbar-inner"
            style={{ position: "relative" }}
          >
            <div
              className="cp-user-sidebar-menu scrollbar-inner scroll-content"
              style={{
                height: "auto",
                marginBottom: "0px",
                marginRight: "0px",
                maxHeight: "0px",
              }}
            ></div>
            <div className="scroll-element scroll-x">
              <div className="scroll-element_outer">
                <div className="scroll-element_size" />
                <div className="scroll-element_track" />
                <div className="scroll-bar" />
              </div>
            </div>
            <div className="scroll-element scroll-y">
              <div className="scroll-element_outer">
                <div className="scroll-element_size" />
                <div className="scroll-element_track" />
                <div className="scroll-bar" />
              </div>
            </div>
          </div>
        </div>
        <div
          id="notificationShow"
          tabIndex={-1}
          role="dialog"
          aria-labelledby="exampleModalCenterTitle"
          aria-hidden="true"
          className="modal fade"
        >
          <div
            role="document"
            className="modal-dialog modal-lg modal-dialog-centered"
          >
            <div className="modal-content dark-modal">
              <div className="modal-header align-items-center">
                <h5 id="exampleModalCenterTitle" className="modal-title">
                  {t("New Notifications")}
                </h5>
                <button
                  type="button"
                  data-dismiss="modal"
                  aria-label="Close"
                  className="close"
                >
                  <span aria-hidden="true">×</span>
                </button>
              </div>
              <div className="modal-body">
                <div className="hm-form">
                  <div className="row">
                    <div className="col-12">
                      <h6 id="n_title" /> <p id="n_date" /> <p id="n_notice" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div className="cp-user-main-wrapper-dashboard">
          <div className="container-fluid">
            <div
              role="alert"
              id="web_socket_notification"
              className="alert alert-success alert-dismissible fade show d-none"
            >
              <span id="socket_message" />
              <button
                type="button"
                data-dismiss="alert"
                aria-label="Close"
                className="close"
              >
                <span aria-hidden="true">×</span>
              </button>
            </div>
            <div
              id="confirm-modal"
              tabIndex={-1}
              role="dialog"
              aria-labelledby="exampleModalCenterTitle"
              aria-hidden="true"
              className="modal fade"
            >
              <div
                role="document"
                className="modal-dialog modal-dialog-centered"
              >
                <div className="modal-content">
                  <button
                    type="button"
                    data-dismiss="modal"
                    aria-label="Close"
                    className="close"
                  >
                    <img alt="" className="img-fluid" />
                  </button>
                  <div className="text-center">
                    <img
                      src="/add-pockaet-vector.svg"
                      alt=""
                      className="img-fluid img-vector"
                    />
                    <h3 id="confirm-title" />
                  </div>
                  <div className="modal-body">
                    <a
                      id="confirm-link"
                      className="btn btn-block cp-user-move-btn"
                    >
                      {t("Confirm")}
                    </a>
                  </div>
                </div>
              </div>
            </div>
            <div className="cp-user-custom-card exchange-area">
              <div id="dashboard">
                <div className="row">
                  <div className="col-xl-12">
                    <div className="cxchange-summary-wrap">
                      <div className="cxchange-summary-name">
                        <div className="summber-coin-type dropdown">
                          <span
                            className="coin-badge dropdown-toggle"
                            id="dropdownMenuButton"
                            data-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false"
                          >
                            {currentPair.replace(/_/g, "/")}
                          </span>

                          <SelectCurrency />
                        </div>
                      </div>
                      {dashboard?.last_price_data && <CurrencyLevel />}
                    </div>
                  </div>

                  <DashboardBody />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Dashboard;
