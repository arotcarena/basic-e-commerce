import React from 'react';
import { CategoryMenu } from './CategoryMenu';
import { SearchButton } from '../../../UI/Button/SearchButton';
import '../../../styles/Header/HeaderBottom/index.css';

export const HeaderBottom = ({categories, activeCategory, activeSubCategory, onSearchClick, viewSearchButton}) => {

    return (
        <div className="header-bottom">
            <CategoryMenu categories={categories} activeCategory={activeCategory} activeSubCategory={activeSubCategory} />

            {
                viewSearchButton && <SearchButton additionalClass="header-search-link header-bottom-search-link" onClick={onSearchClick} />
            }
        </div>
    )
}

