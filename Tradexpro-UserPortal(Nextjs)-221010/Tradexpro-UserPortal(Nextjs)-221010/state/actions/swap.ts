import { toast } from "react-toastify";
import { getUserCoinForSwap, getRate, swapCoin } from "service/swap";
import { parseCookies } from "nookies";
import { Router } from "next/router";

export const getUserCoinForSwapAction = async (
  setList: any | null,
  ctx: any
) => {
  const cookies = parseCookies(ctx);
  const { data } = await getUserCoinForSwap(cookies.token);

  return data?.wallets ? data?.wallets : false;
};
export const getRateAction = async (
  from_coin_id: number,
  to_coin_id: number,
  amount: number,
  setRate: any | null
) => {
  const data = await getRate(from_coin_id, to_coin_id, amount);
  setRate({
    wallet_rate: data.wallet_rate,
    convert_rate: data.convert_rate,
    rate: data.rate,
    from_wallet: data.from_wallet?.coin_type,
    to_wallet: data.to_wallet?.coin_type,
  });
  return data.convert_rate;
};
export const swapCoinAction = async (
  amount: number,
  from_coin_id: number,
  to_coin_id: number,
  setLoading: any
) => {
  setLoading(true);
  const response = await swapCoin(from_coin_id, to_coin_id, amount);
  if (response.success === true) {
    toast.success(response.message);
  } else {
    toast.error(response.message);
  }
  setLoading(false);
};
