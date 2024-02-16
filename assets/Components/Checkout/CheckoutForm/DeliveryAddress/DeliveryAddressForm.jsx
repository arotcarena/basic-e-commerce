import React from 'react';
import { TextConfig } from '../../../../Config/TextConfig';
import { FormFieldWrapper } from '../../../../UI/Form/FormFieldWrapper';
import * as yup from "yup";
import { FormProvider } from 'react-hook-form';


export const deliveryAddressSchema = yup.object({
    civility: yup.string().required('La civilité est obligatoire').test('custom-validation', 'La valeur est incorrecte', (value) => {
        return [TextConfig.CIVILITY_F, TextConfig.CIVILITY_M].includes(value);
    }),
    firstName: yup.string().max(50, '50 caractères max.').required('Le prénom est obligatoire'),
    lastName: yup.string().max(50, '50 caractères max.').required('Le nom est obligatoire'),
    lineOne: yup.string().max(50, '50 caractères max.').required('L\'adresse est obligatoire'),
    postcode: yup.string().length(5, 'Le code postal doit comporter 5 caractères').required('Le code postal est obligatoire'),
    city: yup.string().max(50, '50 caractères max.').required('La ville est obligatoire'),
    country: yup.string().max(50, '50 caractères max.').required('Le pays est obligatoire'),
}).required();


export const DeliveryAddressForm = ({onSubmit, register, errors, isSubmitting, onCancel}) => {
    return (
        <form onSubmit={onSubmit}>
            <div className="form-group">
                <label className="form-label">Civilité</label>
                <FormFieldWrapper className="checkbox-group" id="civility_m" label={TextConfig.CIVILITY_M}>
                    <input 
                        type="radio" 
                        className="form-control" 
                        id="civility_m" 
                        value={TextConfig.CIVILITY_M}
                        {...register('civility')}  
                    />
                </FormFieldWrapper>
                <FormFieldWrapper className="checkbox-group" id="civility_f" label={TextConfig.CIVILITY_F}>
                    <input 
                        type="radio" 
                        className="form-control" 
                        id="civility_f" 
                        value={TextConfig.CIVILITY_F} 
                        {...register('civility')}
                    />
                </FormFieldWrapper>
                {errors.civility && <div className="form-error">{errors.civility.message}</div>}
            </div>
            <FormFieldWrapper id="firstName" label="Prénom" error={errors.firstName?.message}>
                <input type="text" className="form-control" id="firstName" {...register('firstName')} />
            </FormFieldWrapper>
            <FormFieldWrapper id="lastName" label="Nom" error={errors.lastName?.message}>
                <input type="text" className="form-control" id="lastName" {...register('lastName')} />
            </FormFieldWrapper>
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
            <button type="submit" disabled={isSubmitting}>
            {
                isSubmitting ? 'Chargement...': 'Utiliser'
            }
            </button>
            <button onClick={onCancel} disabled={isSubmitting}>Annuler</button>
        </form>
    )
}