import React from 'react';
import { CloseIcon } from '../Icon/CloseIcon';
import { Button } from './Button';

export const CloseButton = ({onClick, additionalClass, ...props}) => {
    return (
        <Button additionalClass={`icon-button ${additionalClass}`} aria-label="Fermer" onClick={onClick} {...props}> 
            <CloseIcon />
        </Button>
    )
}