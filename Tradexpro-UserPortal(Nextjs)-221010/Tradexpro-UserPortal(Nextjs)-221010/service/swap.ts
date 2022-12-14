import request from "lib/request";

export const getUserCoinForSwap = async (ctxCookie: string) => {
  const { data } = await request.get(`/coin-swap-app`, {
    headers: {
      Authorization: `Bearer ${ctxCookie}`,
    },
  });
  return data;
};
export const getRate = async (
  from_coin_id: number,
  to_coin_id: number,
  amount: number
) => {
  const { data } = await request.get(
    `/get-rate-app?from_coin_id=${from_coin_id}&to_coin_id=${to_coin_id}&amount=${amount}`
  );
  return data;
};
export const getRateSsr = async (
  from_coin_id: number,
  to_coin_id: number,
  amount: number,
  ctxCookie: string
) => {
  const { data } = await request.get(
    `/get-rate-app?from_coin_id=${from_coin_id}&to_coin_id=${to_coin_id}&amount=${amount}`,
    {
      headers: {
        Authorization: `Bearer ${ctxCookie}`,
      },
    }
  );
  return data;
};
export const swapCoin = async (
  from_coin_id: number,
  to_coin_id: number,
  amount: number
) => {
  const { data } = await request.post(`/swap-coin-app`, {
    from_coin_id,
    to_coin_id,
    amount,
  });
  return data;
};
