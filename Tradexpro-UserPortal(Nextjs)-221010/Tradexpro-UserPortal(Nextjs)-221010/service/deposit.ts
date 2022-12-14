import request from "lib/request";

export const currencyDeposit = async () => {
  const { data } = await request.get("/currency-deposit");
  return data;
};

export const getCurrencyDepositRate = async (credential: any) => {
  const { data } = await request.post("/get-currency-deposit-rate", credential);
  return data;
};
export const currencyDepositProcess = async (credential: any) => {
  const { data } = await request.post("/currency-deposit-process", credential);
  return data;
};
// currency - deposit - history;
