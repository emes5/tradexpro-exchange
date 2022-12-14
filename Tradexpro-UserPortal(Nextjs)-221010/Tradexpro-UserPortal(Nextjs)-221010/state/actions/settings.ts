import React, { SetStateAction } from "react";
import { toast } from "react-toastify";
import { commomSettings } from "service/landing-page";
import {
  UserSettingsApi,
  Google2faSetupApi,
  Google2faLoginApi,
  LanguageSetupApi,
  UpdateCurrency,
} from "service/settings";
import { setSettings } from "state/reducer/common";
import { setLoading } from "state/reducer/user";

export const UserSettingsAction =
  (setSettings: React.Dispatch<SetStateAction<object>>) =>
  async (dispatch: any) => {
    dispatch(setLoading(true));
    const settings = await UserSettingsApi();
    setSettings(settings.data);
    dispatch(setLoading(false));
  };

export const UpdateCurrencyAction = (code: any) => async (dispatch: any) => {
  console.log("code from action", code);
  await UpdateCurrency(code);
  const settingResponse = await commomSettings();
  dispatch(setSettings(settingResponse.data));
};
export const Google2faLoginAction = async () => {
  const setup = await Google2faLoginApi();
  if (setup.success) {
    toast.success(setup.message);
  } else {
    toast.error(setup.message);
    return { success: false };
  }

  return setup.data;
};

export const SetupLanguageAction = async (
  credential: { language: string },
  setLanguageSelected: any
) => {
  const language = await LanguageSetupApi(credential);
  if (language.success) {
    toast.success(language.message);
    setLanguageSelected(language?.data?.language);
  } else {
    toast.error(language.message);
  }
};

export const SetupGoogle2faAction = async (
  credentials: {
    setup: string;
    code: string;
    google2fa_secret: string;
  },
  setSettings: any
) => {
  const setup = await Google2faSetupApi(credentials);
  toast.warning(setup.message);
  const settings = await UserSettingsApi();
  setSettings(settings.data);
  return setup.data;
};
