import { configureStore } from "@reduxjs/toolkit";
import { userSlice } from "./reducer/user";
import { exchangeSlice } from "./reducer/exchange";
import { commonSlice } from "./reducer/common";
export const store = configureStore({
  reducer: {
    user: userSlice.reducer,
    exchange: exchangeSlice.reducer,
    common: commonSlice.reducer,
  },
});
export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;
