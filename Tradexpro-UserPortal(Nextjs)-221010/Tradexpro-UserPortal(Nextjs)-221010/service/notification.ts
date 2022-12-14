import request from "lib/request";


export const notification = async () => {
  return request.get("/notifications");
};
export const notificationSeen = async (ids: any) => {
  return request.post("/notification-seen ", { ids: ids });
};
