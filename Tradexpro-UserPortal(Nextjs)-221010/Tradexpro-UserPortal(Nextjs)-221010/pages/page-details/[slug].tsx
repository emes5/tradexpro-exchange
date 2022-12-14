import { formateData } from "common";
import Footer from "components/common/footer";
import { GetServerSideProps } from "next";
import useTranslation from "next-translate/useTranslation";
import React from "react";
import {
  customPage,
  customPageWithSlug,
  landingPage,
} from "service/landing-page";
//@ts-ignore
import sanitizeHtml from "sanitize-html";
const Bannerdetails = ({
  details,
  status,
  customPageData,
  socialData,
  copyright_text,
}: any) => {
  const { t } = useTranslation("common");
  const clean = (dirty: any) => {
    return sanitizeHtml(dirty, {
      allowedTags: [
        "b",
        "i",
        "em",
        "strong",
        "a",
        "font",
        "h1",
        "h2",
        "h3",
        "h4",
        "h5",
        "h6",
        "div",
        "p",
        "br",
        "hr",
        "head",
        "img",
      ],
      allowedAttributes: {
        a: ["href", "target", "style"],
        div: ["href", "target", "style"],
        b: ["href", "target", "style"],
        i: ["href", "target", "style"],
        em: ["href", "target", "style"],
        strong: ["href", "target", "style"],
        font: ["href", "target", "style"],
        h1: ["href", "target", "style"],
        h2: ["href", "target", "style"],
        h3: ["href", "target", "style"],
        h4: ["href", "target", "style"],
        h5: ["href", "target", "style"],
        h6: ["href", "target", "style"],
        p: ["href", "target", "style"],
        img: ["src", "href", "style", "data-filename"],
      },
    });
  };

  if (status === false) {
    return (
      <div className="container ">
        <div className="section-wrapper-withHtml text-center">
          <h4>{t("404 not found")}</h4>
        </div>
      </div>
    );
  }
  return (
    <>
      <div className="container mb-5">
        <div className="section-wrapper-withHtml ">
          <img src={details.image} />
          <h1 className="display-4 mt-3">{details.title}</h1>
          <p className="mt-2 mb-2">
            Last revised: {formateData(details.updated_at)}
          </p>
          <div
            dangerouslySetInnerHTML={{
              // __html: clean(details.description),
              __html: details.description,
            }}
          ></div>
        </div>
      </div>
      <Footer
        customPageData={customPageData}
        socialData={socialData}
        copyright_text={copyright_text}
      />
    </>
  );
};
export const getServerSideProps: GetServerSideProps = async (ctx: any) => {
  const { slug } = ctx.query;
  let response: any;
  const { data: customPageData } = await customPage();
  const { data } = await landingPage();
  try {
    const { data } = await customPageWithSlug(slug);
    response = data;
  } catch (error) {}
  return {
    props: {
      details: response.data,
      status: response.success,
      customPageData: customPageData.data,
      socialData: data.media_list,
      copyright_text: data?.copyright_text,
    },
  };
};
export default Bannerdetails;
