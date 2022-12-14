import {
  WithdrawAndDepositHistoryApi,
  AllBuyOrdersHistoryApi,
  AllSellOrdersHistoryApi,
  AllTransactionHistoryApi,
  CoinConvertHistoryApi,
  currencyDepositHistory,
} from "service/reports";
import React, { Dispatch, SetStateAction } from "react";
import FuzzySearch from "fuzzy-search";
export const WithdrawAndDepositHistoryAction = async (
  type: string,
  per_page: number,
  page: number,
  setReport: React.Dispatch<SetStateAction<object>>,
  setProcessing: React.Dispatch<SetStateAction<boolean>>,
  setStillHistory: React.Dispatch<SetStateAction<boolean>>
) => {
  setProcessing(true);
  const response = await WithdrawAndDepositHistoryApi(type, per_page, page);
  setReport(response.data.histories.data);
  setStillHistory(response.data);
  setProcessing(false);
  return response;
};
export const handleSearch = (
  e: React.ChangeEvent<HTMLInputElement>,
  setSearch: Dispatch<any>,
  json: any,
  setHistory: React.Dispatch<SetStateAction<object>>
) => {
  e.preventDefault();
  setSearch(e.target.value);
  if (!e.target.value) {
    setHistory(json.histories.data);
    return;
  }

  const searcher = new FuzzySearch(
    json.histories.data,
    [
      "id",
      "created_at",
      "address",
      "coin_type",
      "amount",
      "fees",
      "transaction_hash",
      "status",
      "type",
      "processed",
      "price",
      "base_coin",
      "trade_coin",
    ],
    {
      caseSensitive: false,
      sort: true,
    }
  );
  const result = searcher.search(e.target.value);
  setHistory(result);
};

export const handleSwapHistorySearch = (
  e: React.ChangeEvent<HTMLInputElement>,
  setSearch: Dispatch<any>,
  json: any,
  setHistory: React.Dispatch<SetStateAction<object>>
) => {
  e.preventDefault();
  setSearch(e.target.value);
  if (!e.target.value) {
    setHistory(json.list.data);
    return;
  }
  const searcher = new FuzzySearch(
    json.list.data,
    [
      "converted_amount",
      "created_at",
      "fromWallet",
      "from_coin_type",
      "from_wallet.name",
      "to_wallet.coin_type",
      "requested_amount",
      "converted_amount",
      "rate",
    ],
    {
      caseSensitive: false,
      sort: true,
    }
  );
  const result = searcher.search(e.target.value);
  setHistory(result);
};

export const handleSearchItems = (
  e: React.ChangeEvent<HTMLInputElement>,
  setSearch: Dispatch<any>,
  json: any,
  setHistory: React.Dispatch<SetStateAction<object>>
) => {
  e.preventDefault();
  setSearch(e.target.value);
  if (!e.target.value) {
    setHistory(json.items.data);
    return;
  }

  const searcher = new FuzzySearch(
    json.items.data,
    [
      "id",
      "created_at",
      "address",
      "coin_type",
      "amount",
      "fees",
      "transaction_hash",
      "status",
      "type",
      "processed",
      "price",
      "base_coin",
      "trade_coin",
      "transaction_id",
      "last_price",
      "price_order_type",
      "total",
      "time",
    ],
    {
      caseSensitive: false,
      sort: true,
    }
  );
  const result = searcher.search(e.target.value);
  setHistory(result);
};
export const handleSearchItemsCurrency = (
  e: React.ChangeEvent<HTMLInputElement>,
  setSearch: Dispatch<any>,
  json: any,
  setHistory: React.Dispatch<SetStateAction<object>>
) => {
  e.preventDefault();
  setSearch(e.target.value);
  if (!e.target.value) {
    setHistory(json.data);
    return;
  }

  const searcher = new FuzzySearch(
    json.data,
    [
      "currency",
      "created_at",
      "address",
      "coin_type",
      "amount",
      "fees",
      "transaction_hash",
      "status",
      "type",
      "processed",
      "price",
      "base_coin",
      "trade_coin",
      "transaction_id",
      "last_price",
      "price_order_type",
      "total",
      "time",
    ],
    {
      caseSensitive: false,
      sort: true,
    }
  );
  const result = searcher.search(e.target.value);
  setHistory(result);
};

export const AllBuyOrdersHistoryAction = async (
  per_page: number,
  page: number,
  setReport: React.Dispatch<SetStateAction<object>>,
  setProcessing: React.Dispatch<SetStateAction<boolean>>,
  setStillHistory: React.Dispatch<SetStateAction<boolean>>,
  column_name: string,
  order_by: string
) => {
  const response = await AllBuyOrdersHistoryApi(
    per_page,
    page,
    column_name,
    order_by
  );
  setReport(response.data.items.data);
  setStillHistory(response.data);
  return response;
};

export const AllSellOrdersHistoryAction = async (
  per_page: number,
  page: number,
  setReport: React.Dispatch<SetStateAction<object>>,
  setProcessing: React.Dispatch<SetStateAction<boolean>>,
  setStillHistory: React.Dispatch<SetStateAction<boolean>>,
  column_name: string,
  order_by: string
) => {
  const response = await AllSellOrdersHistoryApi(
    per_page,
    page,
    column_name,
    order_by
  );
  setReport(response.data.items.data);
  setStillHistory(response.data);
  return response;
};
export const CurrencyDepositHistoryAction = async (
  per_page: number,
  page: number,
  setReport: React.Dispatch<SetStateAction<object>>,
  setProcessing: React.Dispatch<SetStateAction<boolean>>,
  setStillHistory: React.Dispatch<SetStateAction<boolean>>,
  column_name: string,
  order_by: string
) => {
  const response = await currencyDepositHistory(
    per_page,
    page,
    column_name,
    order_by
  );
  setReport(response.data.data);
  setStillHistory(response.data);
  return response;
};

export const AllTransactionHistoryAction = async (
  per_page: number,
  page: number,
  setReport: React.Dispatch<SetStateAction<object>>,
  setProcessing: React.Dispatch<SetStateAction<boolean>>,
  setStillHistory: React.Dispatch<SetStateAction<boolean>>
) => {
  const response = await AllTransactionHistoryApi(per_page, page);
  setReport(response.data.items.data);
  setStillHistory(response.data);
  return response;
};

export const CoinConvertHistoryAction = async (
  per_page: number,
  page: number,
  setReport: React.Dispatch<SetStateAction<object>>,
  setProcessing: React.Dispatch<SetStateAction<boolean>>,
  setStillHistory: React.Dispatch<SetStateAction<boolean>>
) => {
  const response = await CoinConvertHistoryApi(per_page, page);
  setReport(response.data.list.data);
  setStillHistory(response.data);
  return response;
};
