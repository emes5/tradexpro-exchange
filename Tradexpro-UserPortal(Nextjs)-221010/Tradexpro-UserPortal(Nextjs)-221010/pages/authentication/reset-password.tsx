import type { GetServerSideProps, NextPage } from "next";
import React, { useEffect, useState } from "react";
import { Formik, Field, Form, ErrorMessage } from "formik";
import * as Yup from "yup";
import { authPageRequireCheck } from "middlewares/ssr-authentication-check";
import { ResetPasswordAction } from "state/actions/user";
import Link from "next/link";
//@ts-ignore
import ReCAPTCHA from "react-google-recaptcha";
import { RecapCha } from "service/user";
import useTranslation from "next-translate/useTranslation";
import { useSelector } from "react-redux";
import { RootState } from "state/store";
const ResetPassword: NextPage = () => {
  const { t } = useTranslation("common");
  const [processing, setProcessing] = useState(false);
  const { logo } = useSelector((state: RootState) => state.user);
  const [recaptchaData, setRecaptchaData] = useState<any>({});
    const { settings } = useSelector((state: RootState) => state.common);
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
                    password: "",
                    password_confirmation: "",
                    token: "",
                    recapcha:
                      recaptchaData?.google_recapcha !== "1"
                        ? "ksmaldkmalksmdlkamsdlk"
                        : "",
                  }}
                  validationSchema={Yup.object({
                    email: Yup.string()
                      .email(t("Invalid email address"))
                      .required(t("Email is required")),
                    password: Yup.string()
                      .min(6, t("Password must be at least 6 characters"))
                      .required(t("Password is required")),
                    password_confirmation: Yup.string()
                      .oneOf(
                        [Yup.ref("password"), null],
                        t("Passwords must match")
                      )
                      .required(t("Password confirmation is required")),
                    token: Yup.string().required(t("Token is required")),
                    recapcha: Yup.string()
                      .min(6)
                      .required(t("Recapcha is required")),
                  })}
                  onSubmit={async (values) => {
                    await ResetPasswordAction(values, setProcessing);
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
                          placeholder="Your email here"
                        />
                      </div>

                      <div className="form-group">
                        <Field
                          type="password"
                          name="password"
                          id="password"
                          className={`form-control ${
                            touched.password && errors.password
                              ? "is-invalid"
                              : ""
                          }`}
                          placeholder="Type your password"
                        />
                      </div>
                      {/* <ErrorMessage
                        name="password"
                        component="div"
                        className="red-text"
                      /> */}
                      <div className="form-group">
                        <Field
                          type="password"
                          name="password_confirmation"
                          id="password_confirmation"
                          className={`form-control ${
                            touched.password_confirmation &&
                            errors.password_confirmation
                              ? "is-invalid"
                              : ""
                          }`}
                          placeholder="Type your confirm password"
                        />
                      </div>
                      {/* <ErrorMessage
                        name="password_confirmation"
                        component="div"
                        className="red-text"
                      /> */}

                      <div className="form-group">
                        <Field
                          type="token"
                          name="token"
                          id="token"
                          className={`form-control ${
                            touched.token && errors.token ? "is-invalid" : ""
                          }`}
                          placeholder={t("Your token here")}
                        />
                      </div>
                      {/* <ErrorMessage
                        name="token"
                        component="div"
                        className="red-text"
                      /> */}
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
                          t("Submit")
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
              <a className="auth-logo">
                <img src={settings.logo || ""} className="img-fluid" alt="" />
              </a>
              <p>
                {t("Return to sign in")}
                <Link href="/authentication/signin">
                  <a> {t("Sign in")}</a>
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
  // await authPageRequireCheck(ctx);
  return {
    props: {},
  };
};
export default ResetPassword;
