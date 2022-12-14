import React from "react";
import QRCode from "react-qr-code";

const qr = (value: any) => {
  return <div>{value ? <QRCode value={value.value} size={256} /> : ""}</div>;
};

export default qr;
