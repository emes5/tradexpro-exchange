import type { GetServerSideProps, NextPage } from "next";
import * as Yup from "yup";
import { VerifyEmailAction } from "state/actions/user";
import { useDispatch, useSelector } from "react-redux";
import React, { useEffect, useState } from "react";
import { Formik, Field, Form, ErrorMessage } from "formik";
//@ts-ignore
import ReCAPTCHA from "react-google-recaptcha";
import Link from "next/link";
import { authPageRequireCheck } from "middlewares/ssr-authentication-check";
import { RecapCha } from "service/user";
import useTranslation from "next-translate/useTranslation";
import { RootState } from "state/store";
const Signin: NextPage = () => {
  const { t } = useTranslation("common");
  const { logo } = useSelector((state: RootState) => state.user);
  const { settings } = useSelector((state: RootState) => state.common);
  const [processing, setProcessing] = useState(false);
  const dispatch = useDispatch();
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
                  <h2>{t("Verify Email")}</h2>
                </div>
                <Formik
                  initialValues={{
                    email: "",
                    code: "",
                    recapcha:
                      recaptchaData?.google_recapcha !== "1"
                        ? "ksmaldkmalksmdlkamsdlk"
                        : "",
                  }}
                  validationSchema={Yup.object({
                    email: Yup.string()
                      .email("Invalid email address")
                      .required("Email is required"),
                    code: Yup.string().min(6).required("Code is required"),
                    recapcha: Yup.string()
                      .min(6)
                      .required("Recapcha is required"),
                  })}
                  onSubmit={async (values) => {
                    await dispatch(VerifyEmailAction(values, setProcessing));
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

                      <div className="form-group">
                        <Field
                          type={"number"}
                          name="code"
                          id="code"
                          className={`form-control form-control-password look-pass ${
                            touched.code && errors.code ? "is-invalid" : ""
                          }`}
                          placeholder={t("Your code here")}
                        />
                      </div>
                      <ErrorMessage
                        name="code"
                        component="div"
                        className="red-text"
                      />

                      <div className="form-group">
                        <p className="invalid-feedback">{t("Message")}</p>
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
                        disabled={processing}
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
                          t("Verify Email")
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
              <h3>Welcome To</h3>
              <Link href="/">
                <a className="auth-logo" href="">
                  <img src={settings.logo || ""} className="img-fluid" alt="" />
                </a>
              </Link>
              <Link href="/authentication/signup">
                <p>
                  {t("Don’t have account ?")} <a href=""> {t("Sign Up")}</a>
                </p>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export const getServerSideProps: GetServerSideProps = async (ctx: any) => {
  await authPageRequireCheck(ctx);
  return {
    props: {},
  };
};

export default Signin;
