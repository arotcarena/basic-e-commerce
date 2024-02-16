import React from 'react';
import '../../styles/UI/Container/expandMenu.css';
import { CloseButton } from '../Button/CloseButton';

export const ExpandMenu = ({children, onClose}) => {

    return (
        <div className="expand-menu-container">
            <CloseButton onClick={onClose} additionalClass={"expand-closer"} />
            <div className="expand-menu">
                {children}
            </div>
        </div>
    )
}