import useTranslation from "next-translate/useTranslation";

const ProfileComp = () => {
  const { t } = useTranslation();
  return (
    <div>
      <h1>{t("Profile")}</h1>
      <p>{t("This is the profile page")}</p>
    </div>
  );
};

export default ProfileComp;
