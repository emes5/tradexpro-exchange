import useTranslation from "next-translate/useTranslation";
import React, { useEffect, useState } from "react";
import { useSelector } from "react-redux";
import moment from "moment";
import { notification } from "service/notification";
import { RootState } from "state/store";
const NotificationPage = () => {
  const { t } = useTranslation("common");
  const [notificationData, setNotification] = useState<any>([]);
  const { isLoggedIn } = useSelector((state: RootState) => state.user);
  const getNotifications = async () => {
    const data = await notification();
    setNotification(data.data.data);
  };
  useEffect(() => {
    isLoggedIn && getNotifications();
  }, [isLoggedIn]);
  return (
    <>
      <div className="container notification-continer mb-2">
        <h2 className="section-top-title">{t("All notifications")}</h2>
      </div>
      <div className="container notification-continer">
        {notificationData.map((item: any, index: any) => (
          <div className="notification-container" key={index}>
            <p className="notification-time">
              {moment(item.created_at).format("DD MMM YYYY")}
            </p>
            <div className="notification-body">
              <div className="notificationicon"></div>
              <div>
                <h5 className="text-black">{item?.title}</h5>
                <p className="title-body-notifination text-black">
                  {item?.notification_body}
                </p>
              </div>
            </div>
          </div>
        ))}
      </div>
    </>
  );
};

export default NotificationPage;
