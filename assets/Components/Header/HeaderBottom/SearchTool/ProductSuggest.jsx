import React from 'react';

export const ProductSuggest = ({product, q}) => {

    const fullName = product.fullName.toUpperCase().split(q.toUpperCase()).join('<strong>'+q.toUpperCase()+'</strong>');

    return (
        <a className="cta-container" href={product.target}>
            <div className="cta-img" style={{backgroundImage: `url(${product.firstPicture.path})`}}>
            </div>
            <div className="cta-text" dangerouslySetInnerHTML={{__html: fullName}}>
            </div>
        </a>
    )
}


