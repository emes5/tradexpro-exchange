import request from "lib/request";

export const getReferral = () => {
  return request.get("/referral-app");
};
