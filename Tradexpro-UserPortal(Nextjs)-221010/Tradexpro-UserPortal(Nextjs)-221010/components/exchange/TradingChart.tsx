import * as React from "react";
import styles from "./index.module.css";
import { widget, version } from "../../public/static/charting_library";
import Datafeed from "components/exchange/api";
import {
  DISABLED_FEATURES,
  ENABLED_FEATURES,
  getChartOverrides,
  TIME_FRAMES,
} from "./api/chartConfig";
import { splitPair } from "common";

const supportedResolutions = [
  "1",
  "3",
  "5",
  "15",
  "30",
  "60",
  "120",
  "240",
  "360",
  "D",
  "W",
  "M",
];

function getLanguageFromURL() {
  const regex = new RegExp("[\\?&]lang=([^&#]*)");
  const results = regex.exec(window.location.search);
  return results === null
    ? null
    : decodeURIComponent(results[1].replace(/\+/g, " "));
}
type MyProps = {
  coinpair: any;
};
const pair = localStorage.getItem("current_pair")?.replace("_", "/");
localStorage.setItem("tradingview.ChartDrawingToolbarWidget.visible", "false");
export class TVChartContainer extends React.Component<MyProps> {
 
  static defaultProps = {
    symbol: `:${pair && pair}`,
    interval: "5",

    containerId: "tv_chart_container",
    libraryPath: "/static/charting_library/",
    chartsStorageUrl: "https://saveload.tradingview.com",
    chartsStorageApiVersion: "1.1",
    clientId: "tradingview.com",

    userId: "public_user_id",
    fullscreen: false,
    autosize: true,
    studiesOverrides: {},
  };
  // name = this.props.name;

  tvWidget = null;
  //@ts-ignore
  chartInit = (config) => {
    const tvWidget = new widget(config);
    //@ts-ignore
    this.tvWidget = tvWidget;
  };
  //@ts-ignore
  constructor(props) {
    super(props);
    //@ts-ignore
    this.ref = React.createRef();
  }

  componentDidMount() {
    const widgetOptions = {
      height: 480,
      width: 1400,
      //@ts-ignore
      symbol: this.props.symbol,
      style: 1,
      theme: "dark",
      //@ts-ignore
      datafeed: Datafeed,
      // datafeed: Datafeed,

      //@ts-ignore
      interval: this.props.interval,
      //@ts-ignore
      container: this.ref.current,
      //@ts-ignore
      library_path: this.props.libraryPath,
      //@ts-ignore

      locale: getLanguageFromURL() || "en",
      //@ts-ignore
      charts_storage_url: this.props.chartsStorageUrl,
      //@ts-ignore
      charts_storage_api_version: this.props.chartsStorageApiVersion,
      //@ts-ignore

      client_id: this.props.clientId,
      //@ts-ignore
      user_id: this.props.userId,

      //@ts-ignore
      fullscreen: this.props.fullscreen,
      //@ts-ignore
      autosize: this.props.autosize,
      //@ts-ignore
      studies_overrides: this.props.studiesOverrides,
      overrides: {
        "paneProperties.background": "#131722",
        "paneProperties.vertGridProperties.color": "#363c4e",

        "paneProperties.horzGridProperties.color": "#363c4e",
        "symbolWatermarkProperties.transparency": 90,
        "scalesProperties.textColor": "#AAA",
        //background dark
      },
      drawings_access: { type: "black", tools: [{ name: "Regression Trend" }] },
      //@ts-ignore
      enabled_features: ENABLED_FEATURES,
      //@ts-ignore
      disabled_features: DISABLED_FEATURES,

      //@ts-ignore
      overrides: getChartOverrides(this.props.theme),

      //@ts-ignore
      time_frames: TIME_FRAMES,

      toolbar: false,
    };
    //@ts-ignore
    this.chartInit(widgetOptions);
  }

  componentWillUnmount() {
    if (this.tvWidget !== null) {
      //@ts-ignore
      this.tvWidget.remove();
      this.tvWidget = null;
    }
  }

  render() {
    return (
      <>
        <header className={styles.VersionHeader}>
          <h1>
            TradingView Charting Library and Next.js Integration Example{" "}
            {version()}
          </h1>
        </header>
        {/* @ts-ignore */}
        <div ref={this.ref} className={styles.TVChartContainer} />
      </>
    );
  }
}
