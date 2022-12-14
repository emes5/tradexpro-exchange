import React from "react";

const SelectDeposit = ({
  setSelectedMethod,
  depositInfo,
  selectedMethod,
}: any) => {
  return (
    <div className="select-method">
      {depositInfo?.payment_methods.map((payment: any, index: number) => (
        <div
          className={
            selectedMethod.method === payment.payment_method
              ? "select-method-item-active"
              : "select-method-item"
          }
          key={index}
          onClick={() => {
            setSelectedMethod({
              method: payment.payment_method,
              method_id: depositInfo?.payment_methods.find(
                (info: any) =>
                  parseInt(info.payment_method) ===
                  parseInt(payment.payment_method)
              ).id,
            });
          }}
        >
          {payment.title}
        </div>
      ))}
    </div>
  );
};

export default SelectDeposit;
