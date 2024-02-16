import React from 'react';
import { priceFormater } from '../../../functions/formaters';

export const CartSummaryLine = ({cartLine}) => {
    return (
        <li>
            <div>
                <a href={cartLine.product.target}>
                    <img src={cartLine.product.firstPicture.path} alt={cartLine.product.firstPicture.alt} />
                </a>
                <a href={cartLine.product.target}>{cartLine.product.designation}</a> 
            </div>
            <div>
                Prix unitaire : {priceFormater(cartLine.product.price)}
            </div>
            <div>
                Quantité : {cartLine.quantity}
            </div>
            <div>
                Sous-total : {priceFormater(cartLine.totalPrice)}
            </div>
        </li>
    )
}