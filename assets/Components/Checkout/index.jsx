import React, { useState } from 'react';
import { CartSummary } from './CartSummary';
import { CheckoutForm } from './CheckoutForm';
import { useFetch } from '../../CustomHook/fetch/useFetch';

export const Checkout = () => {

    const [cart, loading, errors] = useFetch('api/cart/getFullCart');

    return (
        <div>
            <h1>Passer Commande</h1>
            <CheckoutForm cart={cart} />
            <CartSummary cart={cart} loading={loading} errors={errors} />
        </div>
    )
}

