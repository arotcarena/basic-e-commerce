import React from 'react';

export const TextField = ({children, additionnalClass, type, name, value, onChange, errors, ...props}) => {

    const handleChange = e => {
        onChange(e.currentTarget.name, e.currentTarget.value);
    }

    return (
        <div className={'form-group' + (errors ? ' is-invalid': '')}>
            <label className="form-label" htmlFor={name}>{children}</label>
            <input className={`form-control ${additionnalClass}`} id={name} type={type} name={name} value={value} onChange={handleChange} {...props} />
            {
                errors && (
                    <ul className="form-error">
                        {errors.map((error, index) => <li key={index}>{error}</li>)}
                    </ul>
                )
            }
        </div>
    )
}


export const TextFieldWithTransform = ({transformer, children, additionnalClass, type, name, value, onChange, errors, ...props}) => {
    
    const handleChange = e => {
        onChange(
            e.currentTarget.name, 
            transformer.reverseTransform(e.currentTarget.value)
        );
    }
    
    return (
        <div className={'form-group' + (errors ? ' is-invalid': '')}>
            <label className="form-label" htmlFor={name}>{children}</label>
            <input className={`form-control ${additionnalClass}`} id={name} type={type} name={name} value={transformer.transform(value)} onChange={handleChange} {...props} />
            {
                errors && (
                    <ul className="form-error">
                        {errors.map((error, index) => <li key={index}>{error}</li>)}
                    </ul>
                )
            }
        </div>
    )
}