import React, { useState } from 'react';
import { HeaderLogo } from '/assets/UI/Logo/HeaderLogo';
import { CategoryLink } from './CategoryLink';
import '/assets/styles/Header/HeaderTop/sideMenu.css';


export const MobileMenu = ({categories}) => {

    //sélection des catégories
    const [selectedCategory, setSelectedCategory] = useState(null);

    return (
        <>
            <div className="side-menu-header">
                <HeaderLogo />
            </div>
            <div className="side-menu-body">
                <nav className="side-menu-nav">
                    {
                        categories.map(
                            category => <CategoryLink key={category.id} category={category} selected={category === selectedCategory} setSelected={setSelectedCategory} />
                        )
                    }
                </nav>
            </div>
        </>
    )
}


