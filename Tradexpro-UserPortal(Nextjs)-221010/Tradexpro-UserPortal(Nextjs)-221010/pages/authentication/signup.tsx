import type { GetServerSideProps, NextPage } from "next";
import * as Yup from "yup";
import React, { useEffect, useState } from "react";
import { Formik, Field, Form } from "formik";
import { SignupAction } from "state/actions/user";
import { useDispatch, useSelector } from "react-redux";
import Link from "next/link";
import { useRouter } from "next/router";
import { authPageRequireCheck } from "middlewares/ssr-authentication-check";
//@ts-ignore
import ReCAPTCHA from "react-google-recaptcha";
import { RecapCha } from "service/user";
import useTranslation from "next-translate/useTranslation";
import { RootState } from "state/store";
const Signup: NextPage = () => {
  const {  logo } = useSelector(
    (state: RootState) => state.user
  );
    const { settings } = useSelector((state: RootState) => state.common);
  const dispatch = useDispatch();
  const { t } = useTranslation("common");
  const [recaptchaData, setRecaptchaData] = useState<any>({});
  const router = useRouter();

  const { ref_code } = router.query;
  const [processing, setProcessing] = useState(false);
  const [showPassword, setShowPassword] = useState({
    password: false,
    confirm_password: false,
  });
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
                  <h2>{t("Sign Up")}</h2>
                  <p>{t("Create a new account")}.</p>
                </div>
                <Formik
                  initialValues={{
                    email: "",
                    first_name: "",
                    last_name: "",
                    password: "",
                    password_confirmation: "",
                    recapcha:
                      recaptchaData?.google_recapcha !== "1"
                        ? "ksmaldkmalksmdlkamsdlk"
                        : "",
                  }}
                  validationSchema={Yup.object({
                    email: Yup.string()
                      .email(t("Invalid email address"))
                      .required(t("Email is required")),
                    first_name: Yup.string()
                      .min(2)
                      .required(t("First name is required")),
                    last_name: Yup.string()
                      .min(2)
                      .required(t("Last name is required")),
                    password: Yup.string()
                      .min(8)
                      .required(t("Password is required")),
                    password_confirmation: Yup.string()
                      .oneOf(
                        [Yup.ref("password"), null],
                        t("Passwords must match")
                      )
                      .required("Confirm password is required"),
                    recapcha: Yup.string()
                      .min(6)
                      .required(t("Recapcha is required")),
                  })}
                  onSubmit={async (values) => {
                    console.log(values, "values");
                    dispatch(SignupAction(values, setProcessing, ref_code));
                  }}
                >
                  {({ errors, touched, setFieldValue }) => (
                    <Form>
                      <div className="form-group">
                        <Field
                          type="text"
                          name="first_name"
                          id="first_name"
                          className={`form-control ${
                            touched.first_name && errors.first_name
                              ? "is-invalid"
                              : ""
                          }`}
                          placeholder={t("Your first name here")}
                        />
                      </div>

                      <div className="form-group">
                        <Field
                          type="text"
                          name="last_name"
                          id="last_name"
                          className={`form-control ${
                            touched.last_name && errors.last_name
                              ? "is-invalid"
                              : ""
                          }`}
                          placeholder={t("Your last name here")}
                        />
                      </div>

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
                          type={showPassword.password ? "text" : "password"}
                          name="password"
                          id="password"
                          className={`form-control form-control-password look-pass ${
                            touched.password && errors.password
                              ? "is-invalid"
                              : ""
                          }`}
                          placeholder="Your password here"
                        />

                        <span
                          className="eye rev"
                          onClick={() =>
                            setShowPassword({
                              ...showPassword,
                              password: !showPassword.password,
                            })
                          }
                        >
                          <i className="fa fa-eye-slash toggle-password"></i>
                        </span>
                      </div>

                      <div className="form-group">
                        <Field
                          type={
                            showPassword.confirm_password ? "text" : "password"
                          }
                          name="password_confirmation"
                          id="password_confirmation"
                          className={`form-control form-control-password look-pass ${
                            touched.password_confirmation &&
                            errors.password_confirmation
                              ? "is-invalid"
                              : ""
                          }`}
                          placeholder={t("Your password here")}
                        />

                        <span
                          className="eye rev"
                          onClick={() =>
                            setShowPassword({
                              ...showPassword,
                              confirm_password: !showPassword.confirm_password,
                            })
                          }
                        >
                          <i className="fa fa-eye-slash toggle-password"></i>
                        </span>
                      </div>

                      <div className="form-group">
                        <label></label>
                        <p className="invalid-feedback">{t("Message")} </p>
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
                          t("Sign Up")
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
              <h3>{t("Welcome To")}</h3>
              <Link href="/">
                <a className="auth-logo" href="">
                  <img src={settings.logo || ""} className="img-fluid" alt="" />
                </a>
              </Link>
              <Link href="/authentication/signin">
                <p>
                  {t("Already have an accoun ?")} <a href=""> {t("Sign In")}</a>
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

export default Signup;
