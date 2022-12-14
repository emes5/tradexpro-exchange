import { createSlice, PayloadAction } from "@reduxjs/toolkit";

export type commonType = {
  settings: {};
};

const initialState: any = {
  settings: {},
};

export const commonSlice = createSlice({
  name: "common",
  initialState,
  reducers: {
    setSettings: (state, action: PayloadAction<commonType>) => {
      state.settings = action.payload;
    },
  },
});

export const { setSettings } = commonSlice.actions;
export default commonSlice.reducer;
