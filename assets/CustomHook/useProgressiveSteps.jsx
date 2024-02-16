import { useEffect, useState } from "react";
import { Security } from "../Config/Security";

/**
 * LES STEPS DOIVENT ETRE DES NUMBER
 * @param {number} initialStep 
 */
export const useProgressiveSteps = (initialStep) => {
    const [step, setStep] = useState(initialStep);
    const [maxStep, setMaxStep] = useState(initialStep);

    const forwardStep = (currentStep) => {
        const nextStep = currentStep + 1;
        if(maxStep > nextStep) {
            setStep(maxStep);
        } else {
            setStep(nextStep);
            setMaxStep(nextStep);
        }
    }

    const selectStep = (step) => {
        if(step > maxStep) {
            setMaxStep(step);
        }
        setStep(step);
    }

    //au chargement de la page on cherche si un step est présent dans sessionStorage
    useEffect(() => {
        if(window.sessionStorage.getItem('check_st'))  {
            const data = Security.decryptToObject(window.sessionStorage.getItem('check_st'));
            setStep(data.step);
            setMaxStep(data.maxStep);
        }
    }, []);

    //à chaque changement de step on persiste dans sessionStorage
    useEffect(() => {
        window.sessionStorage.setItem('check_st', Security.encryptFromObject({step: step, maxStep: maxStep}));            
    }, [step]);

    return [step, selectStep, forwardStep];
}