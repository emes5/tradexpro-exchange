import request from "lib/request";

export const getChartData = (
  interval: any,
  startTime: any,
  endTime: any,
  base: any,
  trade: any
) => {
  return request({
    url: `/get-exchange-chart-data-app?base_coin_id=${base}&trade_coin_id=${trade}&interval=${interval}`,
    method: "GET",
  });
};
