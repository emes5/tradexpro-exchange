import request from "lib/request";

export const SigninApi = async (credentials: {
  email: string;
  password: string;
}) => {
  const { data } = await request.post("/sign-in", credentials);
  return data;
};

export const SignupApi = async (
  credentials: {
    email: string;
    password: string;
    first_name: string;
    last_name: string;
    password_confirmation: string;
    recapcha: string;
  },
  ref_code: any
) => {
  const finalCredential = {
    email: credentials.email,
    password: credentials.password,
    first_name: credentials.first_name,
    last_name: credentials.last_name,
    password_confirmation: credentials.password_confirmation,
    recapcha:credentials.recapcha,
    ref_code: ref_code,
  };
  const { data } = await request.post("/sign-up", finalCredential);
  return data;
};

export const ForgotPasswordApi = async (credentials: { email: string }) => {
  const { data } = await request.post("/forgot-password", credentials);
  return data;
};

export const ResetPasswordApi = async (credentials: {
  email: string;
  password: string;
  password_confirmation: string;
  token: string;
}) => {
  const { data } = await request.post("/reset-password", credentials);
  return data;
};

export const GetUserInfoByToken = async () => {
  const { data } = await request.get("/profile");
  return data;
};

export const GetUserInfoByTokenServer = async (ctxCookie: string) => {
  const { data } = await request.get("/profile", {
    headers: {
      Authorization: `Bearer ${ctxCookie}`,
    },
  });
  return data;
};

export const UpdateUserInfoByToken = async (Credentials: any) => {
  const { data } = await request.post("/update-profile", Credentials);
  return data;
};

export const SendPhoneVerificationSms = async () => {
  const { data } = await request.post("/send-phone-verification-sms");
  return data;
};

export const PhoneVerify = async (verify_code: number) => {
  const { data } = await request.post("/phone-verify", {
    verify_code,
  });
  return data;
};
export const ChangePassword = async (credentials: {
  old_password: string;
  password: string;
  password_confirmation: string;
}) => {
  const { data } = await request.post("/change-password", credentials);
  return data;
};

export const UploadNid = async (Files: any) => {
  const { data } = await request.post("/upload-nid", Files);
  return data;
};

export const UploadPassport = async (Files: any) => {
  const { data } = await request.post("/upload-passport", Files);
  return data;
};

export const UploadDrivingLicence = async (Files: any) => {
  const { data } = await request.post("/upload-driving-licence", Files);
  return data;
};

export const KycDetailsApi = async () => {
  const { data } = await request.get("/kyc-details");
  return data;
};
export const G2fVerifyApi = async (credential: any) => {
  const { data } = await request.post("/g2f-verify", credential);
  return data;
};

export const RecapCha = async () => {
  const { data } = await request.get("/recaptcha-settings");
  return data;
};

export const verifyEmailApi = async (credential: any) => {
  const { data } = await request.post("/verify-email", {
    email: credential.email,
    verify_code: credential.code,
    recapcha: credential.recapcha,
  });
  return data;
};
