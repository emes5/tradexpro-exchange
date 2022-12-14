import Loading from "components/common/loading";
import { SSRAuthCheck } from "middlewares/ssr-authentication-check";
import type { GetServerSideProps, NextPage } from "next";
import useTranslation from "next-translate/useTranslation";
import { useEffect, useState } from "react";
import { toast } from "react-toastify";
import { getReferral } from "service/refer";
const Referral: NextPage = () => {
  const [referral, setReferral] = useState<any>();
  const [allData, setAllData] = useState<any>();
  const [loading, setLoading] = useState(true);
  const { t } = useTranslation("common");

  useEffect(() => {
    getReferral().then((res) => {
      const code = res.data.data.url;
      setReferral(
        process.env.NEXT_PUBLIC_HOSTED_CLIENT_URL +
          "authentication/signup?" +
          code
      );
      setAllData(res.data.data);
      setLoading(false);
    });
    return () => {
      setReferral(null);
    };
  }, []);
  return (
    <div className="referral-area">
      <div className="section-top-wrap mb-25">
        <div className="profle-are-top">
          {loading && <Loading />}
          <div className="container">
            <h2 className="section-top-title">{t("Referrals")}</h2>
            <div className="invite-friends">
              <h4>{t("Invite your friends")}</h4>
              <div className="input-group">
                <input
                  type="url"
                  className="form-control"
                  id="url"
                  defaultValue={referral}
                  readOnly
                />
                <button
                  type="button"
                  className="btn copy-url-btn"
                  onClick={() => {
                    navigator.clipboard.writeText(referral);
                    toast.success(t("Copied to clipboard"));
                  }}
                >
                  <i className="fa fa-clone" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div className="container">
        <div className="section-wrapper rounded-sm">
          <div className="rewards-inviter mb-25">
            <div className="single-item">
              <h4>{t("Total Rewards")}</h4>
              <h2>
                {allData?.total_reward} <span />
              </h2>
            </div>
            <div className="single-item">
              <h4>{t("Total Invited")}</h4>
              <h2>{allData?.count_referrals}</h2>
            </div>
          </div>
          <div className="referrals-table">
            <h4 className="section-title-medium">{t("My Referrals")}</h4>
            <div className="table-responsive">
              <table
                className="table cp-user-custom-table table-borderless text-center dataTable no-footer"
                id="DataTables_Table_0"
              >
                <thead>
                  <tr>
                    <th
                      className="referral-level"
                      rowSpan={1}
                      colSpan={1}
                      aria-label="Level 1"
                    >
                      {t("Level 1")}
                    </th>
                    <th
                      className="referral-level"
                      rowSpan={1}
                      colSpan={1}
                      aria-label="Level 2"
                    >
                      {t("Level 2")}
                    </th>
                    <th
                      className="referral-level"
                      rowSpan={1}
                      colSpan={1}
                      aria-label="Level 3"
                    >
                      {t("Level 3")}
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr id="" role="row referral" className="">
                    <td className="referral-text">
                      {allData?.referralLevel[1]}
                    </td>
                    <td className="referral-text">
                      {allData?.referralLevel[2]}
                    </td>
                    <td className="referral-text">
                      {allData?.referralLevel[3]}
                    </td>
                  </tr>
                  {allData?.referralLevel.length == 0 && (
                    <td colSpan={5} className="text-center referral-text">
                      <b>{t("No Data available")}</b>
                    </td>
                  )}

                  <tr>
                    <td colSpan={3} />
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div className="referrals-table">
            <h4 className="section-title-medium">{t("My References")}</h4>
            <div className="table-responsive">
              <table className="table dataTable cp-user-custom-table table-borderless text-center">
                <thead>
                  <tr>
                    <th className="">{t("Full Name")}</th>
                    <th className="">{t("Email")}</th>
                    <th className="">{t("Level")}</th>
                    <th className="">{t("Joining Date")}</th>
                  </tr>
                </thead>
                <tbody>
                  {allData?.referrals?.map((data: any, index: number) => (
                    <tr key={index}>
                      <td className="referral-text">{data?.full_name}</td>
                      <td className="referral-text">{data?.email}</td>
                      <td className="referral-text">{data?.level}</td>
                      <td className="referral-text">{data?.joining_date}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
          <div className="my-earnings-table">
            <h4 className="section-title-medium">{t("My Earnings")}</h4>
            <div className="table-responsive">
              <table className="table dataTable cp-user-custom-table table-borderless text-center">
                <thead>
                  <tr>
                    <th>{t("Coin type")}</th>
                    <th>{t("Amount")}</th>
                    <th>{t("Transaction Id")}</th>
                    <th>{t("Level")}</th>
                  </tr>
                </thead>
                <tbody>
                  {allData?.monthlyEarningHistories?.map(
                    (data: any, index: number) => (
                      <tr key={index}>
                        <td>{data?.coin_type}</td>
                        <td>{data?.amount}</td>
                        <td>{data?.transaction_id}</td>
                        <td>{data?.level}</td>
                      </tr>
                    )
                  )}
                  {allData?.monthlyEarningHistories.length === 0 && (
                    <tr>
                      <td colSpan={6} className="text-center referral-text">
                        <b>{t("No Data available")}</b>
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
export const getServerSideProps: GetServerSideProps = async (ctx: any) => {
  await SSRAuthCheck(ctx, "/user/referral");
  return {
    props: {},
  };
};

export default Referral;
