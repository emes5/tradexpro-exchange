import type { GetServerSideProps, NextPage } from "next";
import { SSRAuthCheck } from "middlewares/ssr-authentication-check";
import { useEffect, useState } from "react";
import {
  UserSettingsAction,
  Google2faLoginAction,
  UpdateCurrencyAction,
} from "state/actions/settings";
import GoogleAuthModal from "components/settings/GoogleAuthModal";
import { useDispatch, useSelector } from "react-redux";
import { RootState } from "state/store";
import useTranslation from "next-translate/useTranslation";

const Settings: NextPage = () => {
  const dispatch = useDispatch();
  const { t } = useTranslation("common");
  const [settings, setSettings] = useState<any>();
  const { settings: settingsReducer } = useSelector(
    (state: RootState) => state.common
  );
  useEffect(() => {
    dispatch(UserSettingsAction(setSettings));

    return () => {
      setSettings(null);
    };
  }, []);
  return (
    <div className="page-wrap">
      <div className="page-main-content">
        <div className="container-fluid">
          <div className="section-top-wrap mb-25">
            <div className="profle-are-top">
              <h2 className="section-top-title mb-0">
                {t("Security Settings")}
              </h2>
            </div>
          </div>
        </div>
        <div className="setting-area">
          <div className="container">
            <div className="section-top-wrap ">
              <div className="row">
                <div className="col-xl-6 mb-xl-0 mb-4">
                  <div className="cp-user-setting-card">
                    <div className="card-body">
                      <div className="cp-user-card-header-area">
                        <div className="cp-user-title">
                          <h4>{t("Google Authentication Settings")}</h4>
                        </div>
                      </div>
                      <div className="cp-user-setting-card-inner">
                        <div className="cp-user-auth-icon">
                          <img src="/gauth.svg" className="img-fluid" alt="" />
                        </div>
                        <div className="cp-user-content mb-0">
                          <h5>{t("Authenticator app")}</h5>
                          <p>
                            {t(
                              "Use the Authenticator app to get free verification codes, even when your phone is offline. Available for Android and iPhone."
                            )}
                          </p>
                        </div>

                        <div className="cp-user-content mt-0">
                          {settings?.user?.google2fa === 0 ? (
                            <a
                              href=""
                              data-toggle="modal"
                              data-target="#exampleModal"
                              className="btn cp-user-setupbtn"
                            >
                              {t("Set up")}
                            </a>
                          ) : (
                            <a
                              href=""
                              data-toggle="modal"
                              data-target="#exampleModal"
                              className=""
                            >
                              {t("Remove Google Authentication")}
                            </a>
                          )}

                          <GoogleAuthModal
                            settings={settings}
                            setSettings={setSettings}
                          />
                        </div>
                        <div className="cp-user-content">
                          <h5>{t("Security")}</h5>
                          <p>
                            {parseInt(settings?.user?.g2f_enabled) === 1
                              ? t(
                                  "Please turn off this option to disable two factor authentication"
                                )
                              : t(
                                  "Please turn on this option to enable two factor authentication"
                                )}
                          </p>
                          <form>
                            <input type="hidden" name="" defaultValue="" />
                            <label className="switch">
                              <input
                                type="checkbox"
                                name="google_login_enable"
                                checked={
                                  parseInt(settings?.user?.g2f_enabled) === 1
                                    ? true
                                    : false
                                }
                                onChange={async (e) => {
                                  const settingsResponse =
                                    await Google2faLoginAction();
                                  if (settingsResponse?.success === false)
                                    return;
                                  setSettings({
                                    ...settings,
                                    user: settingsResponse,
                                  });
                                }}
                              />
                              <span className="slider round" />
                            </label>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div className="col-xl-6 mb-xl-0 mb-4">
                  <div className="cp-user-setting-card">
                    <div className="card-body">
                      <div className="cp-user-card-header-area">
                        <div className="cp-user-title">
                          <h4>{t("Preference Settings")}</h4>
                        </div>
                      </div>
                      <div className="cp-user-setting-card-inner cp-user-setting-card-inner-preference">
                        <div className="cp-user-content">
                          <div className="form-group">
                            <label>{t("Currency")}</label>
                            <div className="cp-user-preferance-setting">
                              <select
                                name="currency"
                                className="form-control"
                                onChange={(e) => {
                                  dispatch(
                                    UpdateCurrencyAction(e.target.value)
                                  );
                                }}
                              >
                                {settings?.fiat_currency?.map(
                                  (currency: any, index: any) => (
                                    <option
                                      key={index}
                                      selected={
                                        settingsReducer.currency ===
                                        currency.code
                                      }
                                      defaultChecked={
                                        settingsReducer.currency ===
                                        currency.lang
                                      }
                                      value={currency.code}
                                    >
                                      {currency.name}
                                    </option>
                                  )
                                )}
                              </select>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
export const getServerSideProps: GetServerSideProps = async (ctx: any) => {
  await SSRAuthCheck(ctx, "/user/settings");
  return {
    props: {},
  };
};

export default Settings;
