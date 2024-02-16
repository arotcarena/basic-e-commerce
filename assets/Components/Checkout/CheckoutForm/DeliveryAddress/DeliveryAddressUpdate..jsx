import React, { useState } from 'react';
import { useFormWithValidation } from '../../../../CustomHook/form/useFormWithValidation';
import { ApiError } from '../../../../functions/api';
import { DeliveryAddressForm, deliveryAddressSchema } from './DeliveryAddressForm';


/**
 * 
 * @param {Object} deliveryAddress (defaultValues)
 * @returns 
 */
export const DeliveryAddressUpdate = ({mainSubmit, update, address, onCancel}) => {
    const { register, handleSubmit, errors, isSubmitting } = useFormWithValidation(deliveryAddressSchema, address);

    const onSubmit = async formData => {
        try {
            await update(formData, address.id); 
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