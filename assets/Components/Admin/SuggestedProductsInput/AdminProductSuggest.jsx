import React from 'react';

export const AdminProductSuggest = ({product, q, onSelect, selected}) => {

    const fullName = product.fullName.toUpperCase().split(q.toUpperCase()).join('<strong>'+q.toUpperCase()+'</strong>');

    const handleClick = e => {
        e.preventDefault();
        onSelect(product);
    }

    return (
        <a className={'admin-suggest-item' + (selected ? ' selected': '')} href="#" onClick={handleClick} role="option" aria-selected={selected}>
            <div className="admin-suggest-img" style={{backgroundImage: `url(${product.firstPicture.path})`}}>
            </div>
            <div className="admin-suggest-text" dangerouslySetInnerHTML={{__html: fullName}}>
            </div>
        </a>
    )
}


