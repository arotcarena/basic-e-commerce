import React from 'react';
import { HeaderTop } from './HeaderTop';
import { HeaderBottom } from './HeaderBottom';
import { SearchTool } from './HeaderBottom/SearchTool';
import { createPortal } from 'react-dom';
import { useOpenState } from '../../CustomHook/useOpenState';
import '../../styles/Header/index.css';


export const Header = ({categories, activeCategory, activeSubCategory, viewSearchButton}) => {

    const [searchToolIsOpen, openSearchTool, closeSearchTool] = useOpenState();

    return (
        <>
            <HeaderTop categories={categories} onSearchClick={openSearchTool} viewSearchButton={viewSearchButton} />
            <HeaderBottom categories={categories} activeCategory={activeCategory} activeSubCategory={activeSubCategory} onSearchClick={openSearchTool} viewSearchButton={viewSearchButton} />

            {
                searchToolIsOpen && createPortal(
                    <SearchTool close={closeSearchTool} />,
                    document.body
                )
            }
        </>
    )
};




