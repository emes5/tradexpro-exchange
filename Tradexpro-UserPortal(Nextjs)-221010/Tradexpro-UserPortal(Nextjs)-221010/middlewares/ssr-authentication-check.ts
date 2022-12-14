import { parseCookies } from "nookies";
import { GetUserInfoByTokenServer } from "service/user";

export const SSRAuthCheck = async (ctx: any, redirect: string) => {
  try {
    const cookies = parseCookies(ctx);
    if (cookies.token) {
      ctx.token = cookies.token;
      const profile: any = await GetUserInfoByTokenServer(cookies.token);
      if (profile?.success === false && profile?.g2f_enabled === "1") {
        ctx.res.writeHead(302, {
          Location: "/authentication/g2f-verify",
        });
        ctx.res.end();
        return;
      }
    } else {
      if (redirect) {
        ctx.res.writeHead(302, {
          Location: "/authentication/signin" + "?redirect=" + redirect,
        });
        ctx.res.end();
      }
      return false;
    }
  } catch (error) {}
};

export const authPageRequireCheck = async (ctx: any) => {
  try {
    const cookies = parseCookies(ctx);
    if (cookies.token) {
      const profile: any = await GetUserInfoByTokenServer(cookies.token);

      if (profile?.success === false && profile?.g2f_enabled === "1") {
        ctx.res.writeHead(302, {
          Location: "/authentication/g2f-verify",
        });
        ctx.res.end();
        return;
      }
    }
    if (cookies.token) {
      ctx.res.writeHead(302, {
        Location: "/exchange/dashboard",
      });
      ctx.res.end();
    }
  } catch (error) {}
};
export const g2fPageRequireCheck = async (ctx: any) => {
  const cookies = parseCookies(ctx);
  if (cookies.g2f_required !== "true") {
    ctx.res.writeHead(302, {
      Location: "/exchange/dashboard",
    });
    ctx.res.end();
  }
};
