import type { GetServerSideProps, NextPage } from "next";
import ProfileSidebar from "layout/profile-sidebar";
import { SSRAuthCheck } from "middlewares/ssr-authentication-check";
import { RootState } from "state/store";
import { useDispatch, useSelector } from "react-redux";
import { useState } from "react";
import {
  SendPhoneVerificationSmsAction,
  VerifyPhoneAction,
} from "state/actions/user";
import Link from "next/link";
import useTranslation from "next-translate/useTranslation";

const PhoneVerification: NextPage = () => {
  const { t } = useTranslation("common");
  const { user } = useSelector((state: RootState) => state.user);
  const [ShowOtpSection, setShowOtpSection] = useState(false);
  const [processing, setProcessing] = useState(false);
  const [verifyProcess, setVerifyProcess] = useState(false);
  const [otp, setOtp] = useState<number>(0);
  const sendCode = () => {
    SendPhoneVerificationSmsAction(setShowOtpSection, setProcessing);
  };
  const verifyCode = () => {
    VerifyPhoneAction(otp, setVerifyProcess, setShowOtpSection);
  };
  return (
    <div className="page-wrap">
      <ProfileSidebar />
      <div className="page-main-content">
        <div className="container-fluid">
          <div className="section-top-wrap mb-25">
            <div className="profle-are-top">
              <h2 className="section-top-title mb-0">
                {t("Phone Verification")}
              </h2>
            </div>
          </div>
          <div className="reset-password-area">
            <h4 className="section-title-medium">{t("Verify Phone")} </h4>
            <div className="section-wrapper">
              <div className="row">
                <div className="col-lg-6 col-md-8">
                  <div className="user-profile-form">
                    <div className="form-group">
                      <label htmlFor="number">{t("Phone number")}</label>
                      {user?.phone ? (
                        <div className="code-list">
                          <input
                            type="text"
                            value={user?.phone}
                            className="form-control"
                            disabled
                          />
                          <button
                            className="btn profile-edit-btn phn-verify-btn"
                            onClick={sendCode}
                          >
                            {processing ? (
                              <>
                                <span
                                  className="spinner-border spinner-border-sm mr-2"
                                  role="status"
                                  aria-hidden="true"
                                ></span>
                                <span>{t("Please wait")}</span>
                              </>
                            ) : ShowOtpSection ? (
                              t("Resend SMS")
                            ) : (
                              t("Send SMS")
                            )}
                          </button>
                          <p>{t("Did not receive code?")}</p>
                        </div>
                      ) : (
                        <div className="code-list">
                          <p>
                            {t("Please add mobile no. first from")}
                            <Link href="/user/edit-profile">
                              {t("edit profile")}
                            </Link>
                          </p>
                        </div>
                      )}
                    </div>

                    {ShowOtpSection && (
                      <div className="form-group">
                        <label htmlFor="number">{t("Verify Code")}</label>
                        <div className="code-list">
                          <input
                            type="text"
                            className="form-control"
                            id=""
                            onChange={(event) => {
                              setOtp(parseInt(event.target.value));
                            }}
                          />
                          <button
                            type="submit"
                            className="btn profile-edit-btn phn-verify-btn"
                            onClick={verifyCode}
                          >
                            {verifyProcess ? (
                              <>
                                <span
                                  className="spinner-border spinner-border-sm mr-2"
                                  role="status"
                                  aria-hidden="true"
                                ></span>
                                <span>{t("Please wait")}</span>
                              </>
                            ) : (
                              t("Verify")
                            )}
                          </button>
                        </div>
                      </div>
                    )}
                  </div>
                </div>
                <div className="col-lg-6 col-md-4">
                  <div className="reset-password-right text-center">
                    <img
                      src="/phone-verification.svg"
                      alt="phone-verification"
                    />
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
  await SSRAuthCheck(ctx, "/user/phone-verification");
  return {
    props: {},
  };
};

export default PhoneVerification;
