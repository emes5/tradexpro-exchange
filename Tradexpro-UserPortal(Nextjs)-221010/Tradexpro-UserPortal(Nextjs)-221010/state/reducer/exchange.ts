import { createSlice, PayloadAction } from "@reduxjs/toolkit";

export type ExchangeState = {
  dashboard: any;
  currentPair: string;
  OpenBookBuy: Array<any>;
  OpenBooksell: Array<any>;
  openOrderHistory: Array<any>;
  sellOrderHistory: Array<any>;
  buyOrderHistory: Array<any>;
  tradeOrderHistory: Array<any>;
  publicTradesDashboard: Array<any>;
  marketTrades: Array<any>;
};
const initialState: ExchangeState = {
  dashboard: {},
  currentPair: "",
  OpenBookBuy: [],
  OpenBooksell: [],
  openOrderHistory: [],
  sellOrderHistory: [],
  buyOrderHistory: [],
  tradeOrderHistory: [],
  publicTradesDashboard: [],
  marketTrades: [],
};

export const exchangeSlice = createSlice({
  name: "exchange",
  initialState,
  reducers: {
    setSellPrice: (state: any, action: any) => {
      state.dashboard.order_data.sell_price = action.payload;
    },
    setBuyPrice: (state: any, action: any) => {
      state.dashboard.order_data.buy_price = action.payload;
    },
    setDashboard: (state: any, action: PayloadAction<ExchangeState>) => {
      state.dashboard = action.payload;
    },
    setCurrentPair: (state: any, action: PayloadAction<string>) => {
      state.currentPair = action.payload;
    },
    setOpenBookBuy: (state: any, action: PayloadAction<Array<any>>) => {
      state.OpenBookBuy = action.payload;
    },
    setOpenBooksell: (state: any, action: PayloadAction<Array<any>>) => {
      state.OpenBooksell = action.payload;
    },
    setOpenOrderHistory: (state: any, action: PayloadAction<Array<any>>) => {
      state.openOrderHistory = action.payload;
    },
    setSellOrderHistory: (state: any, action: PayloadAction<Array<any>>) => {
      state.sellOrderHistory = action.payload;
    },
    setBuyOrderHistory: (state: any, action: PayloadAction<Array<any>>) => {
      state.buyOrderHistory = action.payload;
    },
    setTradeOrderHistory: (state: any, action: PayloadAction<Array<any>>) => {
      state.tradeOrderHistory = action.payload;
    },
    setPublicTradesDashboard: (
      state: any,
      action: PayloadAction<Array<any>>
    ) => {
      state.publicTradesDashboard = action.payload;
    },
    setAllmarketTrades: (state: any, action: PayloadAction<Array<any>>) => {
      state.marketTrades = action.payload;
    },
  },
});

export const {
  setSellPrice,
  setBuyPrice,
  setDashboard,
  setCurrentPair,
  setOpenBookBuy,
  setOpenBooksell,
  setOpenOrderHistory,
  setSellOrderHistory,
  setBuyOrderHistory,
  setTradeOrderHistory,
  setPublicTradesDashboard,
  setAllmarketTrades,
} = exchangeSlice.actions;

export default exchangeSlice.reducer;
