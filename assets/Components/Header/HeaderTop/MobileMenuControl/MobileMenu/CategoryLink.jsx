import React from 'react';
import { ExpandMoreIcon } from '../../../../../UI/Icon/ExpandMoreIcon';


export const CategoryLink = ({category, selected, setSelected}) => {
    const handleClick = e => {
        e.preventDefault();
        if(selected) {
            setSelected(null);
        } else {
            setSelected(category);
        }
    }
    return (
        <>
            <button onClick={handleClick} className={'side-menu-link' + (selected ? ' selected': '')}>
                <span>{category.name.split('_').join(' ')}</span>
                <ExpandMoreIcon />
            </button>
            {
                selected && (
                    category.subCategories.map(subCategory => <SubCategoryLink key={subCategory.id} subCategory={subCategory} />)
                )
            }
        </>
    )
}

const SubCategoryLink = ({subCategory}) => {
    return (
        <a href={subCategory.target} className="side-menu-sublink">{subCategory.name.split('_').join(' ')}</a>
    )
}