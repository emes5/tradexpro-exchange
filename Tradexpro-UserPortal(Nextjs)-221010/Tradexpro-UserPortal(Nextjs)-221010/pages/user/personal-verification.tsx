import type { GetServerSideProps, NextPage } from "next";
import ProfileSidebar from "layout/profile-sidebar";
import { SSRAuthCheck } from "middlewares/ssr-authentication-check";
import NidModal from "components/profile/personal-verification/NidModal";

import { useEffect, useState } from "react";
import { useDispatch } from "react-redux";
import { getKycDetailsAction } from "state/actions/user";
import useTranslation from "next-translate/useTranslation";

const PersonalVerification: NextPage = () => {
  const { t } = useTranslation("common");
  const [type, setType] = useState<string>("");
  const [kycDetails, setKycDetails] = useState<any>();
  const dispatch = useDispatch();
  useEffect(() => {
    dispatch(getKycDetailsAction(setKycDetails));
  }, []);
  return (
    <div className="page-wrap">
      <ProfileSidebar />
      <div className="page-main-content">
        <div className="container-fluid">
          <div className="section-top-wrap mb-25">
            <div className="profle-are-top">
              <h2 className="section-top-title">
                {t("Personal Verification")}
              </h2>
            </div>
          </div>
          <NidModal type={type} kycDetails={kycDetails} />
          <div className="profile-area">
            <h4 className="section-title-medium">{t("Verification")}</h4>
            <div className="section-wrapper">
              <div className="row">
                <div className="col-lg-12">
                  <div className="cp-user-profile-header">
                    <h5>{t("Select Your ID Type")}</h5>
                  </div>
                  <div className="cp-user-profile-info-id-type">
                    <div
                      className="id-card-type"
                      onClick={() => setType("nid")}
                    >
                      <div
                        className="id-card"
                        data-toggle="modal"
                        data-target=".cp-user-idverifymodal"
                      >
                        <img
                          src="/cards/nid.svg"
                          className="img-fluid"
                          alt=""
                        />
                      </div>
                      <div className="card-bottom">
                        <span className="text-warning">
                          {t("Not Submitted")}
                        </span>
                        <h5>{t("National Id Card")}</h5>
                      </div>
                    </div>
                    <div
                      className="id-card-type"
                      onClick={() => setType("passport")}
                    >
                      <div
                        className="id-card"
                        data-toggle="modal"
                        data-target=".cp-user-idverifymodal"
                      >
                        <img
                          src="/cards/passport.svg"
                          className="img-fluid"
                          alt=""
                        />
                      </div>
                      <div className="card-bottom">
                        <span className="text-warning">
                          {t("Not Submitted")}
                        </span>
                        <h5>{t("Passport")}</h5>
                      </div>
                    </div>
                    <div
                      className="id-card-type"
                      onClick={() => setType("driving-licence")}
                    >
                      <div
                        className="id-card"
                        data-toggle="modal"
                        data-target=".cp-user-idverifymodal"
                      >
                        <img
                          src="/cards/driving-license.svg"
                          className="img-fluid"
                          alt=""
                        />
                      </div>
                      <div className="card-bottom">
                        <span className="text-warning">
                          {t("Not Submitted")}
                        </span>
                        <h5>{t("Driving License")}</h5>
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
  await SSRAuthCheck(ctx, "/user/personal-verification");
  return {
    props: {},
  };
};

export default PersonalVerification;
