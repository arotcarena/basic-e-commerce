import React from 'react';

export const FormButton = ({children, additionalClass, loading, ...props}) => {

    return (
        <button type="submit" className={`button ${additionalClass}`} {...props}>
        {
            loading ? <span>Chargement...</span>: children
        }
        </button>
    )
}

