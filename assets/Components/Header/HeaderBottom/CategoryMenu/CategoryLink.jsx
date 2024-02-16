import React from 'react';
import { SubCategoryLink } from './SubCategoryLink';
import { ExpandMenu } from '../../../../UI/Container/ExpandMenu';

export const CategoryLink = ({category, setSelected, selected, active, activeSubCategory}) => {

    const handleOpenExpand = () => {
        setSelected(category);
    };
    const handleKeyDown = e => {
        if(e.key === 'Enter') {
            e.preventDefault();
            e.stopPropagation();
            handleOpenExpand();
        }
    };
    const handleCloseExpand = () => {
        setSelected(null);
    };
    return (
        <div role="button" tabIndex="0" className={'header-bottom-link' + (active ? ' active': '')} 
            onMouseOver={handleOpenExpand} onClick={handleOpenExpand} onKeyDown={handleKeyDown} onMouseLeave={handleCloseExpand}
            >
            { category.name.split('_').join(' ') }
            
            {
                selected && category.subCategories.length > 0 && (
                    <ExpandMenu onClose={handleCloseExpand}>
                        <nav className="header-bottom-subnav">
                        {
                            category.subCategories.map(
                                subCategory => <SubCategoryLink key={subCategory.id} subCategory={subCategory} active={subCategory.id ===  parseInt(activeSubCategory)} />
                            )
                        }
                        </nav>
                    </ExpandMenu>
                )
            }
        </div>
    )
}