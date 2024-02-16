import { useForm } from "react-hook-form";
import { yupResolver } from '@hookform/resolvers/yup';
import { useEffect } from "react";


export const useFormWithValidation = (validationSchema, defaultValues = {}) => {
    const { register, handleSubmit, setValue, formState:{ errors, isSubmitting } } = useForm({
        mode: 'onTouched',
        resolver: yupResolver(validationSchema)
      });

    

    //pour remplir le formulaire avec les valeurs par défaut quand on clique sur Edit
    useEffect(() => {
            for(const [key, value] of Object.entries(defaultValues)) {
                setValue(key, value);
            }
    }, []);

    return { register, handleSubmit, errors, isSubmitting }
}