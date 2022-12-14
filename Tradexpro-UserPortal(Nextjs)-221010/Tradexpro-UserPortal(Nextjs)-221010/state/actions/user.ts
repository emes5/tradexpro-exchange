import {
  ForgotPasswordApi,
  SigninApi,
  SignupApi,
  GetUserInfoByToken,
  ResetPasswordApi,
  UpdateUserInfoByToken,
  SendPhoneVerificationSms,
  PhoneVerify,
  ChangePassword,
  UploadNid,
  UploadPassport,
  UploadDrivingLicence,
  KycDetailsApi,
  G2fVerifyApi,
  verifyEmailApi,
} from "service/user";
import request from "lib/request";
import { login, setAuthenticationState, setUser } from "state/reducer/user";
import Router from "next/router";
import { toast } from "react-toastify";
import Cookies from "js-cookie";
import { Dispatch, SetStateAction } from "react";
import {
  setBuyOrderHistory,
  setOpenOrderHistory,
  setSellOrderHistory,
  setTradeOrderHistory,
} from "state/reducer/exchange";

export const VerifyEmailAction =
  (credentials: { email: string; code: any; recapcha:string }, setProcessing: any) =>
  async (dispatch: any) => {
    setProcessing(true);
    const response: any = await verifyEmailApi(credentials);
    let responseMessage = response.message;
    if (response.success === true) {
      toast.success(responseMessage, {
        position: "top-right",
        autoClose: 5000,
        hideProgressBar: false,
        closeOnClick: true,
        pauseOnHover: true,
        draggable: true,
        progress: undefined,
        className: "dark-toast",
      });
      Router.push("/authentication/signin");
    } else if (response.success === false) {
      dispatch(setAuthenticationState(false));
      toast.error(responseMessage, {
        position: "top-right",
        autoClose: 10000,
        hideProgressBar: false,
        closeOnClick: true,
        pauseOnHover: true,
        draggable: true,
        progress: undefined,
        className: "dark-toast",
      });
    }
    setProcessing(false);
    return response;
  };

export const SigninAction =
  (credentials: { email: string; password: string }, setProcessing: any) =>
  async (dispatch: any) => {
    setProcessing(true);
    const response = await SigninApi(credentials);
    const responseMessage = response.message;

    if (response.success === true) {
      dispatch(login(response.user));
      if (response.access_token) {
        request.defaults.headers.common[
          "Authorization"
        ] = `Bearer ${response.access_token}`;
      }
      if (response.g2f_enabled === "1") {
        Cookies.set("user-id", response.user.id);
        Cookies.set("g2f_required", "true");
        Router.push("/authentication/g2f-verify");
        return;
      }

      if (response.access_token) {
        Cookies.set("token", response.access_token);
        request.defaults.headers.common[
          "Authorization"
        ] = `Bearer ${response.access_token}`;
      }

      toast.success(responseMessage, {
        position: "top-right",
        autoClose: 5000,
        hideProgressBar: false,
        closeOnClick: true,
        pauseOnHover: true,
        draggable: true,
        progress: undefined,
        className: "dark-toast",
      });

      Router.reload();
    } else {
      dispatch(setAuthenticationState(false));
      toast.error(responseMessage, {
        position: "top-right",
        autoClose: 10000,
        hideProgressBar: false,
        closeOnClick: true,
        pauseOnHover: true,
        draggable: true,
        progress: undefined,
        className: "dark-toast",
      });
    }
    setProcessing(false);
    return response;
  };

export const SignupAction =
  (
    credentials: {
      email: string;
      first_name: string;
      last_name: string;
      password: string;
      password_confirmation: string;
      recapcha: string;
    },
    setProcessing: any,
    ref_code: any
  ) =>
  async (dispatch: any) => {
    setProcessing(true);
    const response = await SignupApi(credentials, ref_code);
    let responseMessage = response.message;
    if (response.success === true) {
      toast.success(responseMessage, {
        position: "top-right",
        autoClose: 5000,
        hideProgressBar: false,
        closeOnClick: true,
        pauseOnHover: true,
        draggable: true,
        progress: undefined,
        className: "dark-toast",
      });
      Router.push("/authentication/verify-email");
    } else if (response.success === false) {
      dispatch(setAuthenticationState(false));
      toast.error(responseMessage, {
        position: "top-right",
        autoClose: 10000,
        hideProgressBar: false,
        closeOnClick: true,
        pauseOnHover: true,
        draggable: true,
        progress: undefined,
        className: "dark-toast",
      });
    }
    setProcessing(false);
    return response;
  };

export const ForgotPasswordAction = async (
  credentials: { email: string },
  setProcessing: any
) => {
  setProcessing(true);
  const response = await ForgotPasswordApi(credentials);

  let responseMessage = response.message;
  if (response.success === true) {
    toast.success(responseMessage, {
      position: "top-right",
      autoClose: 5000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      progress: undefined,
      className: "dark-toast",
    });
    Router.push("/authentication/reset-password");
  } else {
    toast.error(responseMessage, {
      position: "top-right",
      autoClose: 10000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      progress: undefined,
      className: "dark-toast",
    });
  }
  setProcessing(false);
};

export const GetUserInfoByTokenAction = () => async (dispatch: any) => {
  const response = await GetUserInfoByToken();

  if (response.success === true) {
    dispatch(login(response.user));
  } else {
    dispatch(setAuthenticationState(false));
  }
};

export const LogoutAction = () => async (dispatch: any) => {
  Cookies.remove("token");
  dispatch(setAuthenticationState(false));
  dispatch(setSellOrderHistory([]));
  dispatch(setBuyOrderHistory([]));
  dispatch(setTradeOrderHistory([]));
  dispatch(setOpenOrderHistory([]));
  const currentRoute = Router.pathname;
  const splitRoute = currentRoute.split("/");
  if (splitRoute[1] != "exchange") {
    Router.replace("/authentication/signin");
  }
};

export const ResetPasswordAction = async (
  credentials: {
    email: string;
    password: string;
    password_confirmation: string;
    token: string;
  },
  setProcessing: any
) => {
  setProcessing(true);
  const response = await ResetPasswordApi(credentials);
  if (response.success === true) {
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
    Router.replace("/authentication/signin");
  } else {
    toast.error(response.message, {
      position: "top-right",
      autoClose: 10000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      progress: undefined,
      className: "dark-toast",
    });
  }
  setProcessing(false);
};
export const UpdateUserInfoByTokenAction =
  (user: any) => async (dispatch: any) => {
    const response = await UpdateUserInfoByToken(user);
    if (response.success === true) {
      toast.success(response.message, {
        position: "top-right",
        autoClose: 5000,
        hideProgressBar: false,
        closeOnClick: true,
        pauseOnHover: true,
        draggable: true,
        progress: undefined,
      });

      dispatch(setUser(response.user));
    } else {
      toast.error(response.message, {
        position: "top-right",
        autoClose: 10000,
        hideProgressBar: false,
        closeOnClick: true,
        pauseOnHover: true,
        draggable: true,
        progress: undefined,
      });
    }
  };

export const SendPhoneVerificationSmsAction = async (
  setShowOtpSection: Dispatch<SetStateAction<boolean>>,
  setProcessing: Dispatch<SetStateAction<boolean>>
) => {
  setProcessing(true);
  const response = await SendPhoneVerificationSms();
  if (response.success === true) {
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
    setShowOtpSection(true);
  } else {
    toast.error(response.message, {
      position: "top-right",
      autoClose: 10000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      progress: undefined,
    });
    setShowOtpSection(false);
  }
  setProcessing(false);
};

export const VerifyPhoneAction = async (
  code: number,
  setProcessing: Dispatch<SetStateAction<boolean>>,
  setShowOtpSection: Dispatch<SetStateAction<boolean>>
) => {
  setProcessing(true);
  const response = await PhoneVerify(code);
  if (response.success === true) {
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
      autoClose: 10000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      progress: undefined,
    });
  }
  setProcessing(false);
  setShowOtpSection(false);
};

export const ChangePasswordAction =
  (credentials: {
    old_password: string;
    password: string;
    password_confirmation: string;
  }) =>
  async (dispatch: any) => {
    const response = await ChangePassword(credentials);
    if (response.success === true) {
      toast.success(response.message, {
        position: "top-right",
        autoClose: 5000,
        hideProgressBar: false,
        closeOnClick: true,
        pauseOnHover: true,
        draggable: true,
        progress: undefined,
      });
    } else {
      toast.error(response.message, {
        position: "top-right",
        autoClose: 10000,
        hideProgressBar: false,
        closeOnClick: true,
        pauseOnHover: true,
        draggable: true,
        progress: undefined,
      });
    }
  };

export const UploadNidImageAction = async (
  image: any,
  setProcessing: Dispatch<SetStateAction<boolean>>
) => {
  setProcessing(true);
  const response = await UploadNid(image);
  if (response.success === true) {
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
      autoClose: 10000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      progress: undefined,
    });
  }
  setProcessing(false);
};

export const UploadPassportImageAction = async (
  image: any,
  setProcessing: Dispatch<SetStateAction<boolean>>
) => {
  setProcessing(true);
  const response = await UploadPassport(image);
  if (response.success === true) {
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
      autoClose: 10000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      progress: undefined,
    });
  }
  setProcessing(false);
};

export const UploadDrivingLicenceImageAction = async (
  image: any,
  setProcessing: Dispatch<SetStateAction<boolean>>
) => {
  setProcessing(true);
  const response = await UploadDrivingLicence(image);
  if (response.success === true) {
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
      autoClose: 10000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      progress: undefined,
    });
  }
  setProcessing(false);
};
export const getKycDetailsAction =
  (setKycDetails: Dispatch<SetStateAction<null>>) => async (dispatch: any) => {
    const { data } = await KycDetailsApi();
    setKycDetails(data);
  };

export const G2fVerifyAction = (code: any) => async (dispatch: any) => {
  const formData = new FormData();
  const uid: any = Cookies.get("user-id");
  formData.append("code", code);
  formData.append("user_id", uid);
  const response = await G2fVerifyApi(formData);
  if (response.success === true) {
    Cookies.remove("g2f-status");
    Cookies.set("token", response.data.access_token);
    dispatch(setUser(response.data));
    Router.push("/exchange/dashboard");
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
    return true;
  } else {
    toast.error(response.message, {
      position: "top-right",
      autoClose: 10000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      progress: undefined,
    });
    return false;
  }
};
