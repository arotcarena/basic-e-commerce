import React from 'react';

export const FormFieldWrapper = ({children, className, id, label, error}) => {
    return (
        <div className={className ?? 'form-group'}>
            <label className="form-label" htmlFor={id}>{label}</label>
            {children}
            {error && <div className="form-error">{error}</div>}
        </div>
    )
}