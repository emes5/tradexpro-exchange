import type { GetServerSideProps, NextPage } from "next";
import React, { useEffect, useState } from "react";
import { ForgotPasswordAction } from "state/actions/user";
import { Formik, Field, Form } from "formik";
import * as Yup from "yup";
import { authPageRequireCheck } from "middlewares/ssr-authentication-check";
import Link from "next/link";
import { RecapCha } from "service/user";
//@ts-ignore
import ReCAPTCHA from "react-google-recaptcha";
import useTranslation from "next-translate/useTranslation";
import { useSelector } from "react-redux";
import { RootState } from "state/store";
const ForgotPassword: NextPage = () => {
  const { settings } = useSelector((state: RootState) => state.common);
  const { t } = useTranslation("common");
  const [processing, setProcessing] = useState(false);
  const [recaptchaData, setRecaptchaData] = useState<any>({});
  const getRecapcha = async () => {
    const response = await RecapCha();
    setRecaptchaData(response.data);
    return response;
  };
  useEffect(() => {
    getRecapcha();
  }, []);
  return (
    <div
      className="user-content-wrapper"
      style={{
        backgroundImage: `url(${settings.login_background})`,
      }}
    >
      <div className="user-content-inner-wrap">
        <div className="row align-items-center">
          <div className="col-md-6">
            <div className="user-form">
              <div className="user-form-inner">
                <div className="form-top">
                  <h2>{t("Forgot Password ?")}</h2>
                  <p>
                    {t(
                      "Please enter the email address to request a password reset."
                    )}
                  </p>
                </div>
                <Formik
                  initialValues={{
                    email: "",
                    recapcha:
                      recaptchaData?.google_recapcha !== "1"
                        ? "ksmaldkmalksmdlkamsdlk"
                        : "",
                  }}
                  validationSchema={Yup.object({
                    email: Yup.string()
                      .email(t("Invalid email address"))
                      .required(t("Email is required")),
                    recapcha: Yup.string()
                      .min(6)
                      .required(t("Recapcha is required")),
                  })}
                  onSubmit={async (values) => {
                    await ForgotPasswordAction(values, setProcessing);
                  }}
                >
                  {({ errors, touched, setFieldValue }) => (
                    <Form>
                      <div className="form-group">
                        <Field
                          type="email"
                          name="email"
                          id="email"
                          className={`form-control ${
                            touched.email && errors.email ? "is-invalid" : ""
                          }`}
                          placeholder={t("Your email here")}
                        />
                      </div>

                      {recaptchaData?.NOCAPTCHA_SITEKEY &&
                        recaptchaData?.google_recapcha === "1" && (
                          <ReCAPTCHA
                            sitekey={recaptchaData?.NOCAPTCHA_SITEKEY}
                            render="explicit"
                            onChange={(response: any) => {
                              setFieldValue("recapcha", response);
                            }}
                          />
                        )}
                      <button
                        type="submit"
                        className="btn nimmu-user-sibmit-button mt-3"
                      >
                        {processing ? (
                          <>
                            <span
                              className="spinner-border spinner-border-md"
                              role="status"
                              aria-hidden="true"
                            ></span>
                            <span>{t("Please wait")}</span>
                          </>
                        ) : (
                          t("Send")
                        )}
                      </button>
                    </Form>
                  )}
                </Formik>
              </div>
            </div>
          </div>
          <div className="col-md-6">
            <div className="user-content-text text-center">
              <h3>{t("Welcome Back To")}</h3>
              <a className="auth-logo" href="">
                <img src={settings.logo || ""} className="img-fluid" alt="" />
              </a>
              <p>
                {t("Return to")}
                <Link href="/authentication/signin">
                  <a href="">{t("Sign In")}</a>
                </Link>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
export const getServerSideProps: GetServerSideProps = async (ctx: any) => {
  await authPageRequireCheck(ctx);
  return { props: {} };
};
export default ForgotPassword;
