const withCSS = require("@zeit/next-css");
const withImages = require("next-images");
const nextTranslate = require("next-translate");

module.exports = {
  reactStrictMode: true,
};
module.exports = withCSS({
  cssLoaderOptions: {
    url: false,
  },
});
module.exports = {
  eslint: {
    ignoreDuringBuilds: true,
  },
};
module.exports = {
  webpack: (config, { isServer }) => {
    if (!isServer) {
      config.node = {
        net: "empty",
        tls: "empty",
        dns: "empty",
      };
    }

    return config;
  },
};
module.exports = withImages();
module.exports = nextTranslate({
  reactStrictMode: true,
});
