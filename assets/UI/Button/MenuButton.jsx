import React from 'react';
import { MenuIcon } from '../Icon/MenuIcon';
import { Button } from './Button';

export const MenuButton = ({onClick, additionalClass, ...props}) => {
    return (
        <Button additionalClass={`icon-button ${additionalClass}`} aria-label="Menu" onClick={onClick} {...props}> 
            <MenuIcon />
        </Button>
    )
}