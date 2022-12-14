import axios from "axios";
import Cookie from "js-cookie";

const request = axios.create({
  baseURL: process.env.NEXT_PUBLIC_BASE_URL,
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});

request.interceptors.request.use((config: any) => {
  const token = Cookie.get("token");
  const lang = global.window && window.location.href.split("/")[3];

  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  config.headers.lang = lang ? lang : "en";
  config.headers.userapisecret = process.env.NEXT_PUBLIC_SECRET_KEY;
  return config;
});
request.interceptors.response.use((response: any) => {
  return response;
});

export function apiRequest(base: any, query: any | null) {
  if (query === null) {
    return request(base);
  } else {
    return axios.get(base + query);
  }
}
export default request;
