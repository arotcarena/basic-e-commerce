import React, { memo, useEffect, useState } from 'react';
import { Button } from '../../../../../UI/Button/Button';
import { CloseIcon } from '../../../../../UI/Icon/CloseIcon';
import { priceFormater } from '../../../../../functions/formaters';

export const CartLine = memo(({product, quantity, totalPrice, error, remove, add, less}) => {
    const handleRemove = () => {
        remove(product.id);
    }
    const handleAdd = e => {
        e.preventDefault();
        add(product.id, 1, quantity + 1);
        //obligatoire pour affichage temporaire de l'erreur
        renderError();
    }
    const handleLess = e => {
        e.preventDefault();
        less(product.id, 1, quantity - 1);
        //obligatoire pour affichage temporaire de l'erreur
        renderError();
    }

    //obligatoire pour l'affichage temporaire de l'erreur
    const [errorMessage, setErrorMessage] = useState(null);
    useEffect(() => {
        renderError();
    }, [error]);

    const renderError = () => {
        setErrorMessage(error);
        setTimeout(() => {
            setErrorMessage(null);
        }, 2000);
    }

    return (
        <li className="cart-line">
            <a className="cart-line-img-link" href={product.target}>
                <img className="cart-line-img" src={product.firstPicture.path} alt={product.firstPicture.alt} />
            </a>
            <div className="cart-line-body">
                <h2 className="cart-line-title"><a href={product.target}>{product.designation}</a></h2>
                <p className="cart-line-text cart-line-price">{product.formatedPrice}</p>
                <p className="cart-line-text">
                    Quantité : 
                    <button className="cart-line-minus" onClick={handleLess}>-</button>
                    {quantity}
                    <button className="cart-line-plus" onClick={handleAdd}>+</button>
                </p>
                {
                    errorMessage && (
                        <div className="form-error">{errorMessage}</div>
                    )
                }
                <p className="cart-line-text">
                    Sous-total : {priceFormater(totalPrice)}
                </p>
            </div>
            <Button aria-label="Supprimer" additionalClass="icon-button cart-line-remover" onClick={handleRemove} title="Supprimer">
                <CloseIcon />
            </Button>
        </li>
    )
});