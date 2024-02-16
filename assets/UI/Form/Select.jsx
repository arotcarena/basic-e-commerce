import React from 'react';

export const Select = ({children, name, value, onChange, errors, ...props})  => {
    return (
        <div className={'form-group select-group' + (errors ? ' is-invalid': '')}>
            <select className="form-select" name={name} value={value} onChange={onChange} {...props}>
                {children}
            </select>
        </div>
    )
}

export const Option = ({children, value}) => {
    return (
        <option className="form-option" value={value}>
            {children}
        </option>
    )
}