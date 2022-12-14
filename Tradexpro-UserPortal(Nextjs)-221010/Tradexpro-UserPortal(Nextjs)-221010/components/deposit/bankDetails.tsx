import { copyTextById } from "common";
import useTranslation from "next-translate/useTranslation";
import React from "react";

const BankDetails = ({ bankInfo }: any) => {
  const { t } = useTranslation("common");
  console.log(bankInfo);
  return (
    <div className="bank-container">
      <div className="bank-item">
        <p className="bank-title">
          {t("Bank name")}{" "}
          <span
            className="file-lable copy-btn"
            onClick={() => {
              copyTextById(bankInfo.bank_name);
            }}
          >
            {t("Copy")}
          </span>
        </p>
        <p>{bankInfo.bank_name ?? ""}</p>
      </div>
      <div className="bank-item">
        <p className="bank-title">
          {t("Bank address")}
          <span
            className="file-lable copy-btn ml-2"
            onClick={() => {
              copyTextById(bankInfo.bank_address);
            }}
          >
            {t("Copy")}
          </span>
        </p>
        <p>{bankInfo.bank_address ?? ""}</p>
      </div>
      <div className="bank-item">
        <p className="bank-title">
          {t("Swift code")}{" "}
          <span
            className="file-lable copy-btn"
            onClick={() => {
              copyTextById(bankInfo.swift_code);
            }}
          >
            {t("Copy")}
          </span>
        </p>
        <p>{bankInfo.swift_code ?? ""}</p>
      </div>
      <div className="bank-item">
        <p className="bank-title">
          {t("Account holder name")}{" "}
          <span
            className="file-lable copy-btn"
            onClick={() => {
              copyTextById(bankInfo.account_holder_name);
            }}
          >
            {t("Copy")}
          </span>
        </p>
        <p>{bankInfo.account_holder_name ?? ""}</p>
      </div>
      <div className="bank-item">
        <p className="bank-title">
          {t("Account holder address")}{" "}
          <span
            className="file-lable copy-btn"
            onClick={() => {
              copyTextById(bankInfo.account_holder_address);
            }}
          >
            {t("Copy")}
          </span>
        </p>
        <p>{bankInfo.account_holder_address ?? ""}</p>
      </div>{" "}
      <div className="bank-item">
        <p className="bank-title">
          {t("Account Number")}{" "}
          <span
            className="file-lable copy-btn"
            onClick={() => {
              copyTextById(bankInfo.iban);
            }}
          >
            {t("Copy")}
          </span>
        </p>
        <p>{bankInfo.iban ?? ""}</p>
      </div>
    </div>
  );
};

export default BankDetails;
