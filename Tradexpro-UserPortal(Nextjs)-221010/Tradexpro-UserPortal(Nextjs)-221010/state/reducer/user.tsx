import { createSlice, PayloadAction } from "@reduxjs/toolkit";

export type UserType = {
  user: {};
  isLoggedIn: boolean;
  notification: [];
  logo: string;
};

const initialState: any = {
  user: {},
  isLoggedIn: false,
  isLoading: false,
  notification: [],
  logo: "",
};

export const userSlice = createSlice({
  name: "user",
  initialState,
  reducers: {
    login: (state, action: PayloadAction<UserType>) => {
      state.user = action.payload;
      state.isLoggedIn = true;
    },
    logout: (state) => {
      state.user = {};
      state.isLoggedIn = false;
    },
    setUser: (state, action: PayloadAction<UserType>) => {
      state.user = action.payload;
    },
    setAuthenticationState: (state, action: PayloadAction<boolean>) => {
      state.isLoggedIn = action.payload;
    },
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.isLoading = action.payload;
    },
    setNotification: (state, action: PayloadAction<any>) => {
      state.notification = action.payload;
    },
    setLogo: (state, action: PayloadAction<any>) => {
      state.logo = action.payload;
    },
  },
});

export const { login, logout, setUser, setAuthenticationState, setLoading, setLogo } =
  userSlice.actions;
export default userSlice.reducer;
