import Link from "next/link";
import React from "react";

const NotLoggedin = () => {
  return (
    <div className="notLoggedin">
      <Link href="/authentication/signin">
        <span className="colorTheme mr-2">Login </span>
      </Link>
      or{" "}
      <Link href="/authentication/signup">
        <span className="colorTheme ml-2 mr-2"> Sign up</span>
      </Link>
      to trade
    </div>
  );
};

export default NotLoggedin;
