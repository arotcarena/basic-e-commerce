import React from 'react';
import { CartSummaryLine } from './CartSummaryLine';
import { priceFormater } from '../../../functions/formaters';
import '../../../styles/Checkout/cartSummary.css';

export const CartSummary = ({cart, loading, errors}) => {

    return (
        <div>
            <h2>Récapitulatif de la commande</h2>
            {
                loading && <div>Chargement...</div>
            }
            {
                cart && (
                    <div className="cart-summary">
                        <ul>
                            {
                                cart.cartLines.map((cartLine, index) => <CartSummaryLine key={index} cartLine={cartLine} />)
                            }
                        </ul>
                        <div>
                            <div>Total : {priceFormater(cart.totalPrice)}</div>
                        </div>
                    </div>
                )
            }
        </div>
    )

}
