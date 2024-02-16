import React from 'react';
import { STEP_INVOICE_ADDRESS } from '.';
import * as yup from "yup";
import { FormFieldWrapper } from '../../../UI/Form/FormFieldWrapper';
import { useFormWithValidation } from '../../../CustomHook/form/useFormWithValidation';


const invoiceAddressSchema = yup.object({
    lineOne: yup.string().max(50, '50 caractères max.').required('L\'adresse est obligatoire'),
    postcode: yup.string().length(5, 'Le code postal doit comporter 5 caractères').required('Le code postal est obligatoire'),
    city: yup.string().max(50, '50 caractères max.').required('La ville est obligatoire'),
    country: yup.string().max(50, '50 caractères max.').required('Le pays est obligatoire'),
}).required();


const createDefault = (address, defaultAddress, civilState) => {
    if(defaultAddress.firstName === civilState.firstName && defaultAddress.lastName === civilState.lastName) {
        for(const [key, value] of Object.entries(address)) {
            if(value === '') {
                address[key] = defaultAddress[key];
            }
        }
    }
    return address;
}


export const InvoiceAddress = ({edit, invoiceAddress, defaultAddress, civilState, setCheckoutData, selectStep, forwardStep}) => {

    const { register, handleSubmit, errors } = useFormWithValidation(invoiceAddressSchema, createDefault(invoiceAddress, defaultAddress, civilState));

    const onSubmit = formData => {
        setCheckoutData(checkoutData => ({
            ...checkoutData,
            invoiceAddress: formData
        }));
        forwardStep(STEP_INVOICE_ADDRESS);
    };

    const handleEdit = e => {
        e.preventDefault();
        selectStep(STEP_INVOICE_ADDRESS);
    }


    return (
        <div>
            <h3>Adresse de facturation</h3>
            <div>
                <div>civilité : {civilState.civility} </div>
                <div>prénom : {civilState.firstName} </div>
                <div>Nom : {civilState.lastName}</div>
            </div>
            {
                edit
                ?
                (
                    <form onSubmit={handleSubmit(onSubmit)}>
                            
                        <FormFieldWrapper id="lineOne" label="Ligne 1" error={errors.lineOne?.message}>
                            <input type="text" className="form-control" id="lineOne" {...register('lineOne')} />
                        </FormFieldWrapper>
                        <FormFieldWrapper id="lineTwo" label="Ligne 2" error={errors.lineTwo?.message}>
                            <input type="text" className="form-control" id="lineTwo" {...register('lineTwo')} />
                        </FormFieldWrapper>
                        <FormFieldWrapper id="postcode" label="Code postal" error={errors.postcode?.message}>
                            <input type="text" className="form-control" id="postcode" {...register('postcode')} />
                        </FormFieldWrapper>
                        <FormFieldWrapper id="city" label="Ville" error={errors.city?.message}>
                            <input type="text" className="form-control" id="city" {...register('city')} />
                        </FormFieldWrapper>
                        <FormFieldWrapper id="country" label="Pays" error={errors.country?.message}>
                            <input type="text" className="form-control" id="country" {...register('country')} />
                        </FormFieldWrapper>
                        <button type="submit">Valider</button>
                    </form>
                )
                :
                (
                    <div>
                        <div>ligne 1 : {invoiceAddress.lineOne}</div>
                        <div>ligne 2 : {invoiceAddress.lineTwo}</div>
                        <div>code postal : {invoiceAddress.postcode}</div>
                        <div>ville : {invoiceAddress.city}</div>
                        <div>pays : {invoiceAddress.country}</div>
                        <button onClick={handleEdit}>Editer</button>
                    </div>
                )
            }
        </div>
        
    )
}