import React, { useEffect, useState } from "react";
import {
  PaymentElement,
  // LinkAuthenticationElement,
  useStripe,
  useElements
} from "@stripe/react-stripe-js";
import '../../../../styles/Checkout/payment-form.css';
import { ApiError, apiFetch } from "../../../../functions/api";


export const PaymentForm = ({piSecret, checkoutData}) => {
  const stripe = useStripe();
  const elements = useElements();
  // sert pour enregistrer la carte (link)
  // const [email, setEmail] = useState('');
  const [errors, setErrors] = useState(null);
  //inutile voir ci-dessous
  // const [message, setMessage] = useState(null); 
  const [isLoading, setIsLoading] = useState(false);

  //INUTILE CAR JE FAIS UNE REDIRECTION DONC CES MESSAGES NE SERONT PAS AFFICHES 
  // useEffect(() => {
  //   if (!stripe) {
  //     return;
  //   }

  //   const clientSecret = new URLSearchParams(window.location.search).get(
  //     "payment_intent_client_secret"
  //   );

  //   if (!clientSecret) {
  //     return;
  //   }

  //   stripe.retrievePaymentIntent(clientSecret).then(({ paymentIntent }) => {
  //     switch (paymentIntent.status) {
  //       case "succeeded":
  //         setMessage("Paiement réussi !");
  //         break;
  //       case "processing":
  //         setMessage("Paiement en cours. Veuillez rester sur cette page");
  //         break;
  //       case "requires_payment_method":
  //         setMessage("Votre paiement a échoué, veuillez recommencer.");
  //         break;
  //       default:
  //         setMessage("Quelque chose n\'a pas fonctionné.");
  //         break;
  //     }
  //   });
  // }, [stripe]);

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!stripe || !elements) {
      // Stripe.js hasn't yet loaded.
      // Make sure to disable form submission until Stripe.js has loaded.
      return;
    }

    setErrors(null);
    setIsLoading(true);

    //lastVerificationBeforePayment : 
    // on vérifie le stock (s'il est insuffisant ça renvoie une erreur mais avant on met à jour le cart et la purchase) 
    // et si on a pas déjà payé cette purchase
    try {
      await apiFetch('/api/purchase/lastVerificationBeforePayment', {
        method: 'POST',
        body: JSON.stringify({
          piSecret: piSecret,
          checkoutData: checkoutData
        })
      });
    } catch(e) {
      if(e instanceof ApiError) {
        if(e.errors.target) {
          window.location.href = e.errors.target;
        } else {
          setErrors(e.errors);
        } 
      }
      setIsLoading(false);
      return;
    }

    //tentative de paiement
    const { error } = await stripe.confirmPayment({
      elements,
      confirmParams: {
        // Make sure to change this to your payment completion page
        return_url: 'https://localhost:8000/commande-validee/'
      },
    });

    // This point will only be reached if there is an immediate error when
    // confirming the payment. Otherwise, your customer will be redirected to
    // your `return_url`. For some payment methods like iDEAL, your customer will
    // be redirected to an intermediate site first to authorize the payment, then
    // redirected to the `return_url`.
    if (error.type === "card_error" || error.type === "validation_error") {
      setErrors([error.message]);
    } else {
      setErrors(['Le paiement a échoué. Si le problème persiste, contactez votre banque.']);
    }

    setIsLoading(false);
  };


  const paymentElementOptions = {
    layout: "tabs"
  }

  return (
    <form id="payment-form" onSubmit={handleSubmit}>
      {/* <LinkAuthenticationElement
        id="link-authentication-element"
        onChange={(e) => setEmail(e.target.value)}
      /> */}
      <PaymentElement id="payment-element" options={paymentElementOptions} />
      <button disabled={isLoading || !stripe || !elements} id="submit">
        <span id="button-text">
          {
            isLoading ? 'Paiement en cours. Veuillez rester sur cette page': 'Payer'
          }
        </span>
      </button>
      {/* Show any error or success messages */}
      {
        errors && errors.map((error, index) => <div key={index} className="form-error">{error}</div>)
      }
    </form>
  );
}