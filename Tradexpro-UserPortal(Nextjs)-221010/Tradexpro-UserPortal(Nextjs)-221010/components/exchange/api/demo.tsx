// import { TV_CHART } from "service/trading-chart";
import { apiRequest } from "lib/request";
import { getChartData } from "service/trading-chart";
const history: any = {};
const api_root = "https://min-api.cryptocompare.com";

export default {
  history: history,
  //@ts-ignore
  getBars: function (symbolInfo, resolution, from, to, first, limit) {
    const base = localStorage.getItem("current_pair")?.split("_")[0];
    const trade = localStorage.getItem("current_pair")?.split("_")[1];
    const baseId = localStorage.getItem("base_coin_id");
    const tradeId = localStorage.getItem("trade_coin_id");
    var split_symbol = symbolInfo.name.split(/[:/]/);
    const url =
      resolution === "D"
        ? "/data/histoday"
        : resolution >= 60
        ? "/data/histohour"
        : "/data/histominute";
    const qs = {
      e: split_symbol[0],
      fsym: base,
      tsym: trade,
      toTs: to,
      limit: limit ? limit : 2000,
      // aggregate: 1//resolution
    };
    //time stamp

    return apiRequest(
      `${api_root}${url}?`,
      `${qs.e}&fsym=${qs.fsym}&tsym=${qs.tsym}&toTs=${qs.toTs}&limit=${qs.limit}`
    ).then((data: any) => {
      if (data.data.Data.length) {
        const myBars = data.data.Data;
        const klines4800 = [...myBars, ...myBars, ...myBars];
        const bars = klines4800.map((el: any) => ({
          time: el.time * 1000,
          low: el.low,
          high: el.high,
          open: el.open,
          close: el.close,
          volume: el.volumefrom,
        }));
        if (first) {
          const lastBar = bars[bars.length - 1];
          history[symbolInfo.name] = { lastBar };
        }
        return bars;
      }
      return [];
    });
  },
};
