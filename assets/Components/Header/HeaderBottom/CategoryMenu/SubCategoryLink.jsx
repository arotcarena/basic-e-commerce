import React from 'react';

export const SubCategoryLink = ({subCategory, active}) => {
    return (
        <a className={'cta-container' + (active ? ' active': '')} href={subCategory.target}>
            <div className="cta-img" style={{backgroundImage: `url(${subCategory.picture.path})`}}>

            </div>
            <div className="cta-text">
                {subCategory.name.split('_').join(' ')}
            </div>
        </a>
    )
}