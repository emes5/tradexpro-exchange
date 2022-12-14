import useTranslation from "next-translate/useTranslation";
import React from "react";

const Loading = () => {
  const { t } = useTranslation("common");
  return (
    <div className="preloder-area">
      <div className="spinner-border text-warning" role="status">
        <span className="sr-only">{t("Loading...")}</span>
      </div>
      <span>{t("Loading...")}</span>
    </div>
  );
};

export default Loading;
