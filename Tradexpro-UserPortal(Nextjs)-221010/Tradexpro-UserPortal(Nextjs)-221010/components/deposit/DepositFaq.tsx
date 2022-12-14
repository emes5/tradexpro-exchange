import useTranslation from "next-translate/useTranslation";
import React from "react";

const DepositFaq = () => {
  const { t } = useTranslation("common");
  return (
    <div className="m-3">
      <div id="accordion">
        <h4>{t("FAQ")}</h4>
        <div className="faq-body">
          <div className="faq-head" id="headingOne">
            <h5 className="mb-0">
              <button
                className="btn "
                data-toggle="collapse"
                data-target="#collapseOne"
                aria-expanded="true"
                aria-controls="collapseOne"
              >
                How can I use AdvCash to deposit?
              </button>
            </h5>
          </div>
          <div
            id="collapseOne"
            className="collapse show"
            aria-labelledby="headingOne"
            data-parent="#accordion"
          >
            <div className="faq-body">
              You need to verify your ID, phone number and address on Advcash to
              use this payment method.
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default DepositFaq;
