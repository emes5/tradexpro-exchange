import type { NextPage } from "next";
import useTranslation from "next-translate/useTranslation";
import { useState } from "react";

const Index: NextPage = () => {
  const { t } = useTranslation("common");
  const [active, setActive] = useState<number>(1);
  const handleActive = (index: number) => {
    if (index === active) {
      setActive(0);
    } else {
      setActive(index);
    }
  };

  return (
    <div className="container-fluid">
      <div
        className="alert alert-success alert-dismissible fade show d-none"
        role="alert"
        id="web_socket_notification"
      >
        <span id="socket_message" />
        <button
          type="button"
          className="close"
          data-dismiss="alert"
          aria-label="Close"
        >
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div
        className="modal fade"
        id="confirm-modal"
        tabIndex={-1}
        role="dialog"
        aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true"
      >
        <div className="modal-dialog modal-dialog-centered" role="document">
          <div className="modal-content">
            <button
              type="button"
              className="close"
              data-dismiss="modal"
              aria-label="Close"
            ></button>
            <div className="text-center">
              <img
                src="/add-pockaet-vector.svg"
                className="img-fluid img-vector"
                alt=""
              />
              <h3 id="confirm-title" />
            </div>
            <div className="modal-body">
              <a
                id="confirm-link"
                href="#"
                className="btn btn-block cp-user-move-btn"
              >
                {t("Confirm")}
              </a>
            </div>
          </div>
        </div>
      </div>
      <div className="section-top-wrap mb-25">
        <div className="profle-are-top">
          <h2 className="section-top-title mb-0">{t("FAQ")}</h2>
        </div>
      </div>
      <div className="container">
        <div className="section-wrapper">
          <div className="row align-items-center">
            <div className="col-lg-6">
              <div className="accordion" id="accordionExample">
                <div className="cp-user-referral-content ">
                  <div className="card">
                    <div
                      className="card-header"
                      id="headingOne"
                      onClick={() => handleActive(1)}
                    >
                      <h5 className="mb-0 header-align">
                        <button
                          className="btn btn-link collapsed"
                          data-toggle="collapse"
                          data-target="#collapseOne1"
                          aria-expanded="true"
                          aria-controls="collapseOne"
                        >
                          {t("What is Codexpro exchange ?")}
                        </button>
                        <i
                          className={`fas ${
                            active === 1 ? "fa-caret-up" : "fa-caret-down"
                          } mright-5`}
                        ></i>
                      </h5>
                    </div>

                    {active === 1 && (
                      <div
                        id="collapseOne1"
                        className="collapse  show "
                        aria-labelledby="headingOne"
                        data-parent="#accordion"
                      >
                        <div className="card-body">
                          {t(
                            "Aenean condimentum nibh vel enim sodales scelerisque.Mauris quisn pellentesque odio, in vulputate turpis.Integer condimentum eni lorem pellentesque euismod. Nam rutrum accumsan nisl vulputate."
                          )}
                        </div>
                      </div>
                    )}
                  </div>
                </div>
                <div className="cp-user-referral-content">
                  <div className="card">
                    <div
                      className="card-header"
                      id="headingOne"
                      onClick={() => handleActive(2)}
                    >
                      <h5 className="mb-0 header-align">
                        <button
                          className="btn btn-link collapsed"
                          data-toggle="collapse"
                          data-target="#collapseOne2"
                          aria-expanded="true"
                          aria-controls="collapseOne"
                        >
                          {t("How it works ?")}
                        </button>
                        <i
                          className={`fas ${
                            active === 2 ? "fa-caret-up" : "fa-caret-down"
                          } mright-5`}
                        ></i>
                      </h5>
                    </div>
                    {active === 2 && (
                      <div
                        id="collapseOne2"
                        className="collapse show"
                        aria-labelledby="headingOne"
                        data-parent="#accordion"
                      >
                        <div className="card-body">
                          {
                            "Aenean condimentum nibh vel enim sodales scelerisque.Mauris quisn pellentesque odio, in vulputate turpis.Integer condimentum eni lorem pellentesque euismod.Nam rutrum accumsan nisl vulputate."
                          }
                        </div>
                      </div>
                    )}
                  </div>
                </div>
                <div className="cp-user-referral-content">
                  <div className="card">
                    <div
                      className="card-header"
                      id="headingOne"
                      onClick={() => handleActive(3)}
                    >
                      <h5 className="mb-0 header-align">
                        <button
                          className="btn btn-link collapsed"
                          data-toggle="collapse"
                          data-target="#collapseOne3"
                          aria-expanded="true"
                          aria-controls="collapseOne"
                        >
                          {t("What is the workflow ?")}
                        </button>
                        <i
                          className={`fas ${
                            active === 3 ? "fa-caret-up" : "fa-caret-down"
                          } mright-5`}
                        ></i>
                      </h5>
                    </div>
                    {active === 3 && (
                      <div
                        id="collapseOne3"
                        className="collapse show"
                        aria-labelledby="headingOne"
                        data-parent="#accordion"
                      >
                        <div className="card-body">
                          {
                            "Aenean condimentum nibh vel enim sodales scelerisque.Mauris quisn pellentesque odio, in vulputate turpis.Integer condimentum eni lorem pellentesque euismod.Nam rutrum accumsan nisl vulputate."
                          }
                        </div>
                      </div>
                    )}
                  </div>
                </div>
                <div className="cp-user-referral-content">
                  <div className="card">
                    <div
                      className="card-header"
                      id="headingOne"
                      onClick={() => handleActive(4)}
                    >
                      <h5 className="mb-0 header-align">
                        <button
                          className="btn btn-link collapsed"
                          data-toggle="collapse"
                          data-target="#collapseOne4"
                          aria-expanded="true"
                          aria-controls="collapseOne"
                        >
                          {t("How i place a order ?")}
                        </button>
                        <i
                          className={`fas ${
                            active === 4 ? "fa-caret-up" : "fa-caret-down"
                          } mright-5`}
                        ></i>
                      </h5>
                    </div>
                    {active === 4 && (
                      <div
                        id="collapseOne4"
                        className="collapse show"
                        aria-labelledby="headingOne"
                        data-parent="#accordion"
                      >
                        <div className="card-body">
                          {t(
                            "Aenean condimentum nibh vel enim sodales scelerisque.Mauris quisn pellentesque odio, in vulputate turpis.Integer condimentum eni lorem pellentesque euismod.Nam rutrum accumsan nisl vulputate."
                          )}
                        </div>
                      </div>
                    )}
                  </div>
                </div>
                <div className="cp-user-referral-content">
                  <div className="card">
                    <div
                      className="card-header"
                      id="headingOne"
                      onClick={() => handleActive(5)}
                    >
                      <h5 className="mb-0 header-align">
                        <button
                          className="btn btn-link collapsed"
                          data-toggle="collapse"
                          data-target="#collapseOne5"
                          aria-expanded="true"
                          aria-controls="collapseOne"
                        >
                          {t("How i make a withdrawal ?")}
                        </button>
                        <i
                          className={`fas ${
                            active === 5 ? "fa-caret-up" : "fa-caret-down"
                          } mright-5`}
                        ></i>
                      </h5>
                    </div>
                    {active === 5 && (
                      <div
                        id="collapseOne5"
                        className="collapse show"
                        aria-labelledby="headingOne"
                        data-parent="#accordion"
                      >
                        <div className="card-body">
                          {t(
                            "Aenean condimentum nibh vel enim sodales scelerisque.Mauris quisn pellentesque odio, in vulputate turpis.Integer condimentum eni lorem pellentesque euismod.Nam rutrum accumsan nisl vulputate."
                          )}
                        </div>
                      </div>
                    )}
                  </div>
                </div>
                <div className="cp-user-referral-content">
                  <div className="card">
                    <div
                      className="card-header"
                      onClick={() => handleActive(6)}
                    >
                      <h5 className="mb-0 header-align">
                        <button className="btn btn-link ">
                          {t("What about the deposit process ?")}
                        </button>
                        <i
                          className={`fas ${
                            active === 6 ? "fa-caret-up" : "fa-caret-down"
                          } mright-5`}
                        ></i>
                      </h5>
                    </div>
                    {active === 6 && (
                      <div
                        id="collapseOne6"
                        className="collapse show"
                        aria-labelledby="headingOne"
                        data-parent="#accordion"
                      >
                        <div className="card-body">
                          {t(
                            "Aenean condimentum nibh vel enim sodales scelerisque.Mauris quisn pellentesque odio, in vulputate turpis.Integer condimentum eni lorem pellentesque euismod.Nam rutrum accumsan nisl vulputate."
                          )}
                        </div>
                      </div>
                    )}
                  </div>
                </div>
              </div>
            </div>
            <div className="col-lg-6">
              <div className="faq-image text-center">
                <img src="/faq-image.png" alt="faq-image" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Index;
