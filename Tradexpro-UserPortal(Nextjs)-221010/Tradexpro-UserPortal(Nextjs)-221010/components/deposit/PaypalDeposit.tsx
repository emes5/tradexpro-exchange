import {
  PayPalScriptProvider,
  PayPalButtons,
  usePayPalScriptReducer,
} from "@paypal/react-paypal-js";
import { toast } from "react-toastify";
import { PayPalScriptOptions } from "@paypal/paypal-js/types/script-options";
//@ts-ignore
import { PayPalButtonsComponentProps } from "@paypal/paypal-js/types/components/buttons";
import { currencyDepositProcess } from "service/deposit";
import { useRouter } from "next/router";

const paypalScriptOptions: PayPalScriptOptions = {
  //@ts-ignore
  "client-id": process.env.NEXT_PUBLIC_PAYPAL_SECRET_KEY,
  currency: "USD",
};
function Button({ credential, setCredential }: any) {
  /**
   * usePayPalScriptReducer use within PayPalScriptProvider
   * isPending: not finished loading(default state)
   * isResolved: successfully loaded
   * isRejected: failed to load
   */
  const router = useRouter();
  const [{ isPending }] = usePayPalScriptReducer();
  const paypalbuttonTransactionProps: PayPalButtonsComponentProps = {
    style: { layout: "vertical" },
    createOrder(data: any, actions: any) {
      return actions.order.create({
        purchase_units: [
          {
            amount: {
              value: credential.amount,
            },
          },
        ],
      });
    },
    onApprove(data: any, actions: any) {
      /**
       * data: {
       *   orderID: string;
       *   payerID: string;
       *   paymentID: string | null;
       *   billingToken: string | null;
       *   facilitatorAccesstoken: string;
       * }
       */
    //   console.log(data, "datadatadatadata");
    //   alert("Data details: " + JSON.stringify(data, null, 2));
      const credentials = {
        wallet_id: credential.wallet_id,
        payment_method_id: credential.payment_method_id,
        amount: credential.amount,
        currency: credential.currency,
        paypal_token: data.billingToken,
      };
      return actions.order.capture({}).then(async (details: any) => {
        // alert(
        //   "Transaction completed by" +
        //     (details?.payer.name.given_name ?? "No details")
        // );

        const res = await currencyDepositProcess(credentials);
        if (res.success) {
          toast.success(res.message);
          router.push("/user/currency-deposit-history");
        } else {
          toast.error(res.message);
        }
      });
    },
  };
  return (
    <>
      {isPending ? <h2>Load Smart Payment Button...</h2> : null}
      <PayPalButtons {...paypalbuttonTransactionProps} />
    </>
  );
}
export default function PaypalButtons({ credential, setCredential }: any) {
  return (
    <div className="paypal-container">
      <PayPalScriptProvider options={paypalScriptOptions}>
        <Button credential={credential} setCredential={setCredential} />
      </PayPalScriptProvider>
    </div>
  );
}
