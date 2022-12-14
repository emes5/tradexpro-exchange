import {
  appDashboardData,
  buyLimitApp,
  buyMarketApp,
  buyStopLimitApp,
  openBookDashboard,
  ordersHistoryDashboard,
  sellLimitApp,
  sellMarketApp,
  sellStopLimitApp,
  tradesHistoryDashboard,
  marketTradesDashboard,
  cancelOrderApp,
  appDashboardDataWithoutPair,
} from "service/exchange";
import {
  setDashboard,
  setOpenBookBuy,
  setOpenBooksell,
  setOpenOrderHistory,
  setSellOrderHistory,
  setBuyOrderHistory,
  setTradeOrderHistory,
  setAllmarketTrades,
  setCurrentPair,
} from "state/reducer/exchange";
import { toast } from "react-toastify";
import { Dispatch, SetStateAction } from "react";
import Cookies from "js-cookie";

export const initialDashboardCallAction =
  (pair: string, dashboard: any, setisLoading?: any) =>
  async (dispatch: any) => {
    setisLoading && setisLoading(true);
    const token = Cookies.get("token");

    let response;
    if (pair) {
      response = await appDashboardData(pair);
    } else {
      response = await appDashboardDataWithoutPair();
      console.log(
        response.pairs[0].parent_coin_id,
        "asdasdsasdasdasdassdfsdgsrg"
      );
    }

    if (pair) {
      await localStorage.setItem(
        "base_coin_id",
        response.order_data.base_coin_id
      );
      await localStorage.setItem(
        "trade_coin_id",
        response.order_data.trade_coin_id
      );
    } else {
      await localStorage.setItem(
        "base_coin_id",
        response.pairs[0].parent_coin_id
      );
      await localStorage.setItem(
        "trade_coin_id",
        response.pairs[0].child_coin_id
      );
      await localStorage.setItem("current_pair", response.pairs[0].coin_pair);
      dispatch(setCurrentPair(response.pairs[0].coin_pair));
    }

    await dispatch(setDashboard(response));

    const BuyResponse = await openBookDashboard(
      response.order_data.base_coin_id
        ? response.order_data.base_coin_id
        : response.pairs[0].parent_coin_id,
      response.order_data.trade_coin_id
        ? response.order_data.trade_coin_id
        : response.pairs[0].child_coin_id,
      "dashboard",
      "buy",
      50
    );
    dispatch(setOpenBookBuy(BuyResponse?.data?.orders));
    const SellResponse = await openBookDashboard(
      response.order_data.base_coin_id
        ? response.order_data.base_coin_id
        : response.pairs[0].parent_coin_id,
      response.order_data.trade_coin_id
        ? response.order_data.trade_coin_id
        : response.pairs[0].child_coin_id,
      "dashboard",
      "sell",
      50
    );
    dispatch(setOpenBooksell(SellResponse?.data?.orders));
    const marketTradesDashboardResponse = await marketTradesDashboard(
      response.order_data.base_coin_id
        ? response.order_data.base_coin_id
        : response.pairs[0].parent_coin_id,
      response.order_data.trade_coin_id
        ? response.order_data.trade_coin_id
        : response.pairs[0].child_coin_id,
      "dashboard",
      50
    );
    dispatch(
      setAllmarketTrades(marketTradesDashboardResponse.data.transactions)
    );
    if (
      response.order_data.base_coin_id &&
      response.order_data.trade_coin_id &&
      token
    ) {
      const ordersHistoryResponse = await ordersHistoryDashboard(
        response.order_data.base_coin_id,
        response.order_data.trade_coin_id,
        "dashboard",
        "buy_sell"
      );
      dispatch(setOpenOrderHistory(ordersHistoryResponse?.data?.orders));
      const sellOrderHistoryresponse = await ordersHistoryDashboard(
        response.order_data.base_coin_id,
        response.order_data.trade_coin_id,
        "dashboard",
        "sell"
      );
      dispatch(setSellOrderHistory(sellOrderHistoryresponse?.data?.orders));
      const buyOrderHistoryresponse = await ordersHistoryDashboard(
        response.order_data.base_coin_id,
        response.order_data.trade_coin_id,
        "dashboard",
        "buy"
      );
      dispatch(setBuyOrderHistory(buyOrderHistoryresponse?.data?.orders));
      const tradeOrderHistoryResponse = await tradesHistoryDashboard(
        response.order_data.base_coin_id,
        response.order_data.trade_coin_id,
        "dashboard"
      );
      dispatch(
        setTradeOrderHistory(tradeOrderHistoryResponse?.data?.transactions)
      );
    }

    setisLoading && setisLoading(false);
  };

export const initialDashboardCallActionWithToken =
  (pair: string, dashboard: any, setisLoading?: any) =>
  async (dispatch: any) => {
    setisLoading && setisLoading(true);
    const token = Cookies.get("token");

    if (token) {
      const ordersHistoryResponse = await ordersHistoryDashboard(
        dashboard?.order_data?.base_coin_id,
        dashboard?.order_data?.trade_coin_id,
        "dashboard",
        "buy_sell"
      );
      dispatch(setOpenOrderHistory(ordersHistoryResponse?.data?.orders));
      const sellOrderHistoryresponse = await ordersHistoryDashboard(
        dashboard?.order_data?.base_coin_id,
        dashboard?.order_data?.trade_coin_id,
        "dashboard",
        "sell"
      );
      dispatch(setSellOrderHistory(sellOrderHistoryresponse?.data?.orders));
      const buyOrderHistoryresponse = await ordersHistoryDashboard(
        dashboard?.order_data?.base_coin_id,
        dashboard?.order_data?.trade_coin_id,
        "dashboard",
        "buy"
      );
      dispatch(setBuyOrderHistory(buyOrderHistoryresponse?.data?.orders));
      const tradeOrderHistoryResponse = await tradesHistoryDashboard(
        dashboard?.order_data?.base_coin_id,
        dashboard?.order_data?.trade_coin_id,
        "dashboard"
      );
      dispatch(
        setTradeOrderHistory(tradeOrderHistoryResponse?.data?.transactions)
      );
      const marketTradesDashboardResponse = await marketTradesDashboard(
        dashboard?.order_data?.base_coin_id,
        dashboard?.order_data?.trade_coin_id,
        "dashboard",
        50
      );
      dispatch(
        setAllmarketTrades(marketTradesDashboardResponse.data.transactions)
      );
    }
    setisLoading && setisLoading(false);
  };

export const buyLimitAppAction = async (
  amount: number,
  price: number,
  trade_coin_id: string,
  base_coin_id: string,
  setLoading: Dispatch<SetStateAction<boolean>>,
  setBuyCoinData: any
) => {
  setLoading(true);
  const response = await buyLimitApp(
    amount,
    price,
    trade_coin_id,
    base_coin_id
  );
  if (response.status === true) {
    toast.success(response.message, {
      position: "top-right",
      autoClose: 5000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      progress: undefined,
      className: "dark-toast",
    });
    setBuyCoinData({
      amount: 0,
      price: 0,
      total: 0,
    });
  } else {
    toast.error(response.message, {
      position: "top-right",
      autoClose: 5000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      progress: undefined,
      className: "dark-toast",
    });
  }
  setLoading(false);
};
export const buyMarketAppAction = async (
  amount: number,
  price: number,
  trade_coin_id: string,
  base_coin_id: string,
  setLoading: Dispatch<SetStateAction<boolean>>
) => {
  setLoading(true);
  const response = await buyMarketApp(
    amount,
    price,
    trade_coin_id,
    base_coin_id
  );
  if (response.status === true) {
    toast.success(response.message, {
      position: "top-right",
      autoClose: 5000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      progress: undefined,
      className: "dark-toast",
    });
  } else {
    toast.error(response.message, {
      position: "top-right",
      autoClose: 5000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      progress: undefined,
      className: "dark-toast",
    });
  }
  setLoading(false);
};
export const buyStopLimitAppAction = async (
  amount: number,
  total: number,
  limit: number,
  stop: number,
  trade_coin_id: string,
  base_coin_id: string,
  setLoading: Dispatch<SetStateAction<boolean>>
) => {
  setLoading(true);
  const response = await buyStopLimitApp(
    amount,
    limit,
    stop,
    trade_coin_id,
    base_coin_id
  );
  if (response.status === true) {
    toast.success(response.message, {
      position: "top-right",
      autoClose: 5000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      progress: undefined,
      className: "dark-toast",
    });
  } else {
    toast.error(response.message, {
      position: "top-right",
      autoClose: 5000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      progress: undefined,
      className: "dark-toast",
    });
  }
  setLoading(false);
};
export const sellLimitAppAction = async (
  amount: number,
  price: number,
  trade_coin_id: string,
  base_coin_id: string,
  setLoading: Dispatch<SetStateAction<boolean>>,
  setsellCoinData: any
) => {
  setLoading(true);
  const response = await sellLimitApp(
    amount,
    price,
    trade_coin_id,
    base_coin_id
  );
  if (response.status === true) {
    toast.success(response.message, {
      position: "top-right",
      autoClose: 5000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      progress: undefined,
      className: "dark-toast",
    });
    setsellCoinData({
      amount: 0,
      price: 0,
      total: 0,
    });
  } else {
    toast.error(response.message, {
      position: "top-right",
      autoClose: 5000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      progress: undefined,
      className: "dark-toast",
    });
  }
  setLoading(false);
};
export const sellMarketAppAction = async (
  amount: number,
  price: number,
  trade_coin_id: string,
  base_coin_id: string,
  setLoading: Dispatch<SetStateAction<boolean>>
) => {
  setLoading(true);
  const response = await sellMarketApp(
    amount,
    price,
    trade_coin_id,
    base_coin_id
  );
  if (response.status === true) {
    toast.success(response.message, {
      position: "top-right",
      autoClose: 5000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      progress: undefined,
      className: "dark-toast",
    });
  } else {
    toast.error(response.message, {
      position: "top-right",
      autoClose: 5000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      progress: undefined,
      className: "dark-toast",
    });
  }
  setLoading(false);
};
export const sellStopLimitAppAction = async (
  amount: number,
  total: number,
  limit: number,
  stop: number,
  trade_coin_id: string,
  base_coin_id: string,
  setLoading: Dispatch<SetStateAction<boolean>>
) => {
  setLoading(true);
  const response = await sellStopLimitApp(
    amount,
    limit,
    stop,
    trade_coin_id,
    base_coin_id
  );
  if (response.status === true) {
    toast.success(response.message, {
      position: "top-right",
      autoClose: 5000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      progress: undefined,
      className: "dark-toast",
    });
  } else {
    toast.error(response.message, {
      position: "top-right",
      autoClose: 5000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      progress: undefined,
      className: "dark-toast",
    });
  }
  setLoading(false);
};

export const cancelOrderAppAction = async (type: string, id: string) => {
  const response = await cancelOrderApp(type, id);
  if (response.status === true) {
    toast.success(response.message, {
      position: "top-right",
      autoClose: 5000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      progress: undefined,
      className: "dark-toast",
    });
  } else {
    toast.error(response.message, {
      position: "top-right",
      autoClose: 5000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      progress: undefined,
      className: "dark-toast",
    });
  }
};
