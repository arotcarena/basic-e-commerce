import React from 'react';
import { CloseButton } from '../../../UI/Button/CloseButton';

export const SuggestedProductsList = ({products, onRemove, loading}) => {
    if(loading) {
        return <div className="admin-form-info">Chargement...</div>
    }
    return (
        <ul className="admin-suggestedProducts-list">
            {
                products.map(product => <SuggestProductItem key={product.id} product={product} onRemove={onRemove} />)
            }
        </ul>
    )
} 

const SuggestProductItem = ({product, onRemove}) => {
    
    const handleClick = () => {
        onRemove(product);
    }

    return (
        <li key={product.id} className="admin-suggestedProducts-item">
            <div className="admin-suggest-img" style={{backgroundImage: `url(${product.firstPicture.path})`}}></div>
            <div>{product.fullName}</div>
            <CloseButton onClick={handleClick} additionalClass="admin-suggestedProducts-closer" />
        </li>
    )
}