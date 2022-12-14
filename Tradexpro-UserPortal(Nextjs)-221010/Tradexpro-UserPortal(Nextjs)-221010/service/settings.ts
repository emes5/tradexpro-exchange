import request from "lib/request";

export const UserSettingsApi = async () => {
  const { data } = await request.get("/user-setting");
  return data;
};

export const Google2faSetupApi = async (credentials: {
  setup: string;
  code: string;
  google2fa_secret: string;
}) => {
  const { data } = await request.post("/google2fa-setup", credentials);
  return data;
};

export const LanguageListApi = async () => {
  const { data } = await request.get("/language-list");
  return data;
};

export const Google2faLoginApi = async () => {
  const { data } = await request.get("/setup-google2fa-login");
  return data;
};

export const LanguageSetupApi = async (credentials: { language: string }) => {
  const { data } = await request.post("/language-setup", credentials);
  return data;
};

export const UpdateCurrency = async (code: string) => {
  const { data } = await request.post("/update-currency", { code: code });
  return data;
};
