import React, { useEffect, useState } from 'react';
import { loadStripe } from "@stripe/stripe-js";
import { Elements } from "@stripe/react-stripe-js";
import { ApiError, apiFetch } from '../../../../functions/api';
import { PaymentForm } from './PaymentForm';

export const Payment = ({checkoutData, cart}) => {

    const [loading, setLoading] = useState(false);
    const [errors, setErrors] = useState(null);

    // Make sure to call loadStripe outside of a component’s render to avoid
    // recreating the Stripe object on every render.
    // This is your test publishable API key.
    const stripePromise = loadStripe('pk_test_51NDZQnIzjAGtS1EU3vT39agJeqInSEIgbnoLl8t5REgGfG1EkKBjRcGSESIj1A8KP8cVTS4vjDodw5ioMt9nnymA00aN4OhNJu');

    const [clientSecret, setClientSecret] = useState('');

    useEffect(() => {
        (async () => {
            setLoading(true);
            setErrors(null)
            try {
                const totalPrice = cart?.totalPrice;  // cart peut être en cours de chargement (si on actualise la page en étant déjà sur step Payment)
                const data = await apiFetch('/api/payment/createPaymentIntent', {
                    method: 'POST',
                    body: JSON.stringify(totalPrice) // si on envoie totalPrice undefined c'est le server qui se chargera de récupérer le cart
                });
                setClientSecret(data.clientSecret);
            } catch(e) {
                if(e instanceof ApiError) {
                    setErrors(e.errors);
                }
            }
            setLoading(false);
        })();
    }, []);

    const appearance = {
        theme: 'stripe',
    };
    const options = {
        clientSecret,
        appearance,
    };
    

    if(loading) {
        return <div>Chargement...</div>
    }

    if(errors) {
        return <div>{ errors.map((error, index) => <div key={index} className="form-error">{error}</div>) }</div>
    }

    return (
        <div>
            <h3>Réglement</h3>
            {clientSecret && (
                <Elements options={options} stripe={stripePromise}>
                    <PaymentForm piSecret={clientSecret} checkoutData={checkoutData} />
                </Elements>
            )}
        </div>
    )
}