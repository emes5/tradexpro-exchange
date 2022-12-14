import React from "react";
import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";

const SmallLoading = () => {
  return <Skeleton width="100%" height={30} count={1} borderRadius={10} />;
};

export default SmallLoading;
