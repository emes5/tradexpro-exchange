import type { GetServerSideProps, NextPage } from "next";
import ProfileSidebar from "layout/profile-sidebar";
import React from "react";
import * as Yup from "yup";

import { Formik, Field, Form, ErrorMessage } from "formik";
import { SSRAuthCheck } from "middlewares/ssr-authentication-check";
import { ChangePasswordAction } from "state/actions/user";
import { useDispatch } from "react-redux";
import useTranslation from "next-translate/useTranslation";
const PhoneVerification: NextPage = () => {
  const { t } = useTranslation("common");
  const dispatch = useDispatch();
  return (
    <div className="page-wrap">
      <ProfileSidebar />
      <div className="page-main-content">
        <div className="container-fluid">
          <div className="section-top-wrap mb-25">
            <div className="profle-are-top">
              <h2 className="section-top-title mb-0">{t("Reset Password")}</h2>
            </div>
          </div>
          <Formik
            initialValues={{
              old_password: "",
              password: "",
              password_confirmation: "",
            }}
            validationSchema={Yup.object({
              old_password: Yup.string()
                .min(6, "Password must be at least 6 characters")
                .required("Password is required"),
              password: Yup.string()
                .min(6, "Password must be at least 6 characters")
                .required("Password is required"),
              password_confirmation: Yup.string()
                .oneOf([Yup.ref("password"), null], "Passwords must match")
                .required("Password confirmation is required"),
            })}
            onSubmit={async (values) => {
              await dispatch(ChangePasswordAction(values));
            }}
          >
            {({ errors, touched }) => (
              <Form>
                <div className="reset-password-area">
                  <h4 className="section-title-medium">
                    {t("Change Password")}
                  </h4>
                  <div className="section-wrapper">
                    <div className="row">
                      <div className="col-lg-6 col-md-8">
                        <div className="user-profile-form">
                          <div className="form-group">
                            <label>{t("Old password")}</label>
                            <Field
                              name="old_password"
                              id="old_password"
                              type="password"
                              placeholder="Old password"
                              className={`form-control ${
                                touched.old_password && errors.old_password
                                  ? "is-invalid"
                                  : ""
                              }`}
                            />
                          </div>
                          <ErrorMessage
                            name="old_password"
                            component="div"
                            className="red-text"
                          />
                          <div className="form-group">
                            <label>{t("New Password")}</label>
                            <Field
                              name="password"
                              id="password"
                              type="password"
                              placeholder="New Password"
                              className={`form-control ${
                                touched.password && errors.password
                                  ? "is-invalid"
                                  : ""
                              }`}
                            />
                          </div>
                          <ErrorMessage
                            name="password"
                            component="div"
                            className="red-text"
                          />
                          <div className="form-group">
                            <label>{t("Password Conformation")}</label>
                            <Field
                              name="password_confirmation"
                              id="password_confirmation"
                              type="password"
                              placeholder="Re Enter New Password"
                              className={`form-control ${
                                touched.password_confirmation &&
                                errors.password_confirmation
                                  ? "is-invalid"
                                  : ""
                              }`}
                            />
                          </div>
                          <ErrorMessage
                            name="password_confirmation"
                            component="div"
                            className="red-text"
                          />
                          <div className="form-group m-0">
                            <button
                              className="primary-btn-outline"
                              type="submit"
                            >
                              {t("Change Password")}
                            </button>
                          </div>
                        </div>
                      </div>
                      <div className="col-lg-6 col-md-4">
                        <div className="reset-password-right text-center">
                          <img src="/reset-password.svg" alt="reset-password" />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </Form>
            )}
          </Formik>
        </div>
      </div>
    </div>
  );
};
export const getServerSideProps: GetServerSideProps = async (ctx: any) => {
  await SSRAuthCheck(ctx, "/user/change-password");
  return {
    props: {},
  };
};

export default PhoneVerification;
