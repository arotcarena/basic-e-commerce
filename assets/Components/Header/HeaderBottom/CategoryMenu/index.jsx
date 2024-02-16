import React, { useState } from 'react';
import { CategoryLink } from './CategoryLink';
import '../../../../styles/Header/HeaderBottom/categoryMenu.css';

export const CategoryMenu = ({categories, activeCategory, activeSubCategory}) => {
    //sélection des catégories
    const [selectedCategory, setSelectedCategory] = useState(null);


    return (
        <nav className="header-bottom-nav">
            {
                categories.map(
                    category => (
                        <CategoryLink 
                            key={category.id} 
                            category={category} 
                            selected={selectedCategory === category} 
                            active={category.id === parseInt(activeCategory)}
                            activeSubCategory={activeSubCategory}
                            setSelected={setSelectedCategory} 
                            />
                    )
                )
            }
            
        </nav>
    )
}






