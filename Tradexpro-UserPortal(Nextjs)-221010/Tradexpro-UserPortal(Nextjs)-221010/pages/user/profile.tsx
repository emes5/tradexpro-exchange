import type { GetServerSideProps, NextPage } from "next";
import ProfileComp from "components/profile/profile";
import { parseCookies } from "nookies";

import { GetUserInfoByTokenServer } from "service/user";
import ProfileSidebar from "layout/profile-sidebar";
import { SSRAuthCheck } from "middlewares/ssr-authentication-check";
import useTranslation from "next-translate/useTranslation";
const Profile: NextPage = ({ user }: any) => {
  const { t } = useTranslation("common");
  return (
    <div className="page-wrap">
      <ProfileSidebar />
      <div className="page-main-content">
        <div className="container-fluid">
          <div className="section-top-wrap mb-25">
            <div className="profle-are-top">
              <h2 className="section-top-title">
                {user?.first_name + " " + user?.last_name}
              </h2>
              <h3 className="user-mail">{user?.email}</h3>
            </div>
          </div>
          <div className="profile-area">
            <h4 className="section-title-medium">{t("Profile Information")}</h4>
            <div className="section-wrapper">
              <div className="user-profile">
                <div className="row">
                  <div className="col-lg-4 col-md-5">
                    <div className="user-profile-left">
                      <div className="user-thumbnail">
                        <img
                          src={user?.photo}
                          className="img-fluid"
                          alt="user"
                        />
                      </div>
                      <div className="user-profile-content">
                        <h2>{user?.first_name + " " + user?.last_name}</h2>
                        <h4>{user?.email}</h4>
                      </div>
                    </div>
                  </div>
                  <div className="col-lg-8 col-md-7">
                    <div className="user-profile-info">
                      <ul>
                        <li>
                          <span>{t("Name")}</span>
                          <span className="cp-user-dot">:</span>
                          <span>
                            {user?.first_name
                              ? user?.first_name + " " + user?.last_name
                              : "no name"}
                          </span>
                        </li>
                        <li>
                          <span>{t("Country")}</span>
                          <span className="cp-user-dot">:</span>
                          <span>
                            {user?.country_name
                              ? user?.country_name
                              : user?.country
                              ? user?.country
                              : t("No country")}
                          </span>
                        </li>
                        <li>
                          <span>{t("Email")}</span>
                          <span className="cp-user-dot">:</span>
                          <span>
                            {" "}
                            {user?.email ? user?.email : t("No email")}
                          </span>
                        </li>
                        <li>
                          <span>{t("Email Verification")}</span>
                          <span className="cp-user-dot">:</span>
                          <span>
                            {user?.is_verified ? (
                              <span className="badge badge-success">
                                {t("Active")}
                              </span>
                            ) : (
                              <span className="badge badge-danger">
                                {t("Inactive")}
                              </span>
                            )}
                          </span>
                        </li>
                        <li>
                          <span>{t("Phone")}</span>
                          <span className="cp-user-dot">:</span>
                          <span> {user?.phone ? user?.phone : "No phone"}</span>
                        </li>
                      </ul>
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
  await SSRAuthCheck(ctx, "/user/profile");
  const cookies = parseCookies(ctx);
  const response = await GetUserInfoByTokenServer(cookies.token);
  return {
    props: {
      user: response.user,
    },
  };
};
export default Profile;
