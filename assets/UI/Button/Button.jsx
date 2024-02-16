import React from 'react';

export const Button = ({children, onClick, additionalClass, loading, ...props}) => {
    const handleClick = e => {
        e.preventDefault();
        e.stopPropagation();
        onClick();
    }
    const handleKeyDown = e => {
        if(e.key === 'Enter') {
            e.preventDefault();
            e.stopPropagation();
            onClick();
        }
    }
    return (
        <button className={`button ${additionalClass}`} onClick={handleClick} onKeyDown={handleKeyDown} {...props}>
        {
            loading ? <span>Chargement...</span>: children
        }
        </button>
    )
}

