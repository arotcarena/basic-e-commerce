import React from 'react';
import { Button } from './Button';
import { CartIcon } from '../Icon/CartIcon';

export const CartButton = ({children, onClick, additionalClass, ...props}) => {
    return (
        <Button additionalClass={`icon-button ${additionalClass}`} aria-label="Panier" onClick={onClick} {...props}> 
            <CartIcon />
            {children}
        </Button>
    )
}