import React from 'react';
import { STEP_CIVIL_STATE } from '.';
import * as yup from "yup";
import { TextConfig } from '../../../Config/TextConfig';
import { FormFieldWrapper } from '../../../UI/Form/FormFieldWrapper';
import { useFormWithValidation } from '../../../CustomHook/form/useFormWithValidation';
import { apiFetch } from '../../../functions/api';


const civilStateSchema = yup.object({
    civility: yup.string().required('La civilité est obligatoire').test('custom-validation', 'La valeur est incorrecte', (value) => {
        return [TextConfig.CIVILITY_F, TextConfig.CIVILITY_M].includes(value);
    }),
    firstName: yup.string().max(50, '50 caractères max.').required('Le prénom est obligatoire'),
    lastName: yup.string().max(50, '50 caractères max.').required('Le nom est obligatoire')
}).required();




export const CivilState = ({edit, civilState, setCheckoutData, selectStep, forwardStep}) => {

    //form
    const { register, handleSubmit, errors, isSubmitting } = useFormWithValidation(civilStateSchema, civilState);

    const onSubmit = async formData => {
        try {
            await apiFetch('api/user/setCivilState', {
                method: 'POST',
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(formData)
            });
            setCheckoutData(checkoutData => ({
                ...checkoutData,
                civilState: formData
            }));
            forwardStep(STEP_CIVIL_STATE);
        } catch(e) {
            //
        }
    };

    const handleEdit = e => {
        e.preventDefault();
        selectStep(STEP_CIVIL_STATE);
    }

    return (
        <div>
            <h3>Vos informations</h3>
            {
                edit 
                ?
                (
                    <form onSubmit={handleSubmit(onSubmit)}>
                        <div className="form-group">
                            email : {civilState.email}
                        </div>
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
                                    id="firstName" 
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
                        <button type="submit">
                            {
                                isSubmitting ? 'Chargement...': 'Valider'
                            }
                        </button>
                    </form>
                )
                :
                (
                    <div>
                        <div>email : {civilState.email}</div>
                        <div>civilité : {civilState.civility}</div>
                        <div>prénom : {civilState.firstName}</div>
                        <div>nom : {civilState.lastName}</div>
                        <button onClick={handleEdit}>Editer</button>
                    </div>
                )
            }
        </div>
    )
}





