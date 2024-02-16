import React from 'react';
import { SearchIcon } from '../Icon/SearchIcon';
import { Button } from './Button';

export const SearchButton = ({onClick, additionalClass, ...props}) => {
    return (
        <Button additionalClass={`icon-button ${additionalClass}`} aria-label="Rechercher" onClick={onClick} {...props}> 
            <SearchIcon />
        </Button>
    )
}