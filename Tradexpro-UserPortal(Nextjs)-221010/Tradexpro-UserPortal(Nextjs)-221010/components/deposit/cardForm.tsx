import React, { useMemo } from "react";
import { useStripe, useElements, CardElement } from "@stripe/react-stripe-js";

const useOptions = () => {
  const options = useMemo(
    () => ({
      style: {
        base: {
          fontSize: 22,
          color: "#424770",
          letterSpacing: "0.025em",
          // fontFamily: "Source Code Pro, monospace",
          // "::placeholder": {
          //   color: "#aab7c4",
          // },
        },
        invalid: {
          color: "#9e2146",
        },
      },
    }),
    []
  );

  return options;
};

const CardForm = ({ setCredential, credential }: any) => {
  const stripe = useStripe();
  const elements = useElements();
  const options = useOptions();

  const handleSubmit = async (event: any) => {
    event.preventDefault();

    if (!stripe || !elements) {
      // Stripe.js has not loaded yet. Make sure to disable
      // form submission until Stripe.js has loaded.
      return;
    }
    const cardElement = elements.getElement("card");

 
    //@ts-ignore
    const payload = await stripe.createToken(cardElement);

    setCredential({
      ...credential,
      //@ts-ignore
      stripe_token: payload.token.id,
    });
  };

  return (
    <form onSubmit={handleSubmit}>
      <label>
        <CardElement
          //@ts-ignore
          options={options}
          onReady={() => {
            console.log("CardElement [ready]");
          }}
          onChange={(event) => {
            console.log("CardElement [change]", event);
          }}
          onBlur={() => {
            console.log("CardElement [blur]");
          }}
          onFocus={() => {
            console.log("CardElement [focus]");
          }}
        />
      </label>
      <button
        type="submit"
        className="primary-btn-outline mb-3 w-100"
        disabled={!stripe}
      >
        Deposit
      </button>
    </form>
  );
};

export default CardForm;
