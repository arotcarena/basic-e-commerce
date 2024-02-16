import React, { useEffect } from 'react';
import { CartLine } from './CartLine';
import { priceFormater } from '../../../../../functions/formaters';

export const Cart = ({cart, fetchCart, remove, add, less}) => {

    useEffect(() => {
        fetchCart();
    }, []);

    return (
        <div className="cart-modal">
            <div className="cart-header side-menu-header">
                <h1 className="cart-title">
                    Panier ({cart.count ?? '0'})
                </h1>
            </div>
            {
                cart.generalLoading && <div className="cart-sub-header">Chargement...</div>
            }
            
            {
                !cart.generalLoading && cart.lines.length === 0 && <div className="cart-sub-header">Le panier est vide</div>
            }

            {
                cart.lines.length > 0 &&
                (
                    <>
                        <div className="cart-body">
                            <ul className="cart-list">
                                {
                                    cart.lines.map((line) => (
                                        <CartLine 
                                            key={line.product.id} 
                                            product={line.product}
                                            quantity={line.quantity}
                                            totalPrice={line.totalPrice}
                                            error={line.error} 
                                            remove={remove} 
                                            add={add} 
                                            less={less} 
                                            />
                                    ))
                                }
                            </ul>
                        </div>
                        <div className="cart-footer">
                            <p className="cart-total">Total : {priceFormater(cart.totalPrice)}</p>
                            <a href="/passer-commande" className="cart-footer-link">
                                Procéder à l'achat
                            </a>
                        </div>
                    </>
                )
            }
            
        </div>
    )
}