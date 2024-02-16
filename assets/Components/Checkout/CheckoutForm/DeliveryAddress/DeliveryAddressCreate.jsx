import React, { useState } from 'react';
import { useFormWithValidation } from '../../../../CustomHook/form/useFormWithValidation';
import { ApiError } from '../../../../functions/api';
import { DeliveryAddressForm, deliveryAddressSchema } from './DeliveryAddressForm';


/**
 * 
 * @param {Object} deliveryAddress (defaultValues)
 * @returns 
 */
export const DeliveryAddressCreate = ({mainSubmit, create, onCancel}) => {
    const { register, handleSubmit, errors, isSubmitting } = useFormWithValidation(deliveryAddressSchema);
    
    const onSubmit = async formData => {
        try {
            await create(formData); 
        } catch(e) {
            //
        }
        mainSubmit(formData);
    };

    return (
        <DeliveryAddressForm 
            onSubmit={handleSubmit(onSubmit)} 
            register={register} 
            errors={errors} 
            isSubmitting={isSubmitting}
            onCancel={onCancel} 
        />
    )
}