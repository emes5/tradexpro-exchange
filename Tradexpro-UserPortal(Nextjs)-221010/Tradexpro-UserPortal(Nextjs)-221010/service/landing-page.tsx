import request from "lib/request";

export const landingPage = () => {
  return request.get("/landing");
};

export const customPage = () => {
  return request.get("/custom-pages");
};
export const commomSettings = () => {
  return request.get("/common-settings");
};
export const customPageWithSlug = (slug: string) => {
  return request.get(`/pages-details/${slug}`);
};

export const bannerList = () => {
  return request.get("/banner-list");
};
export const announcementList = () => {
  return request.get("/announcement-list");
};

export const featureList = () => {
  return request.get("/feature-list");
};
export const socialMediaList = () => {
  return request.get("/social-media-list");
};

export const bannerDetailsBySlug = (slug: any) => {
  return request.get(`/banner-list/${slug}`);
};
