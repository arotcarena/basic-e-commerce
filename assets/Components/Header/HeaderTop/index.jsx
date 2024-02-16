import React from 'react';
import { HeaderLogo } from '../../../UI/Logo/HeaderLogo';
import { SearchButton } from '../../../UI/Button/SearchButton';
import { CartControl } from './CartControl';
import { MobileMenuControl } from './MobileMenuControl';
import '../../../styles/Header/HeaderTop/index.css';

export const HeaderTop = ({categories, onSearchClick, viewSearchButton}) => {

    return (
        <div className="header-top">
            <div>
                <MobileMenuControl categories={categories} />
                {
                    viewSearchButton && <SearchButton additionalClass="header-search-link header-top-search-link" onClick={onSearchClick} />
                }
            </div>
            
            <HeaderLogo />

            <div>
                <CartControl />
            </div>
        </div>
    )
}