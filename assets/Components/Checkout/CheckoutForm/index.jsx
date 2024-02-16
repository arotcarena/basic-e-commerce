import React, { useEffect, useState } from 'react';
import { CivilState } from './CivilState';
import { DeliveryAddress } from './DeliveryAddress';
import { InvoiceAddress } from './InvoiceAddress';
import { Payment } from './Payment';
import { Security } from '../../../Config/Security';
import { useFetch } from '../../../CustomHook/fetch/useFetch';
import { useProgressiveSteps } from '../../../CustomHook/useProgressiveSteps';
import { apiFetch } from '../../../functions/api';


export const STEP_CIVIL_STATE = 1;
export const STEP_DELIVERY_ADDRESS = 2;
export const STEP_INVOICE_ADDRESS = 3;
export const STEP_PAYMENT = 4;

const checkoutInitialData = {
    civilState: {
        civility: '',
        firstName: '',
        lastName: ''
    },
    deliveryAddress: {
        civility: '',
        firstName: '',
        lastName: '',
        lineOne: '',
        lineTwo: '',
        postcode: '',
        city: '',
        country: ''
    },
    invoiceAddress: {
        lineOne: '',
        lineTwo: '',
        postcode: '',
        city: '',
        country: ''
    },
}


export const CheckoutForm = ({cart}) => {
    
    const [checkoutData, setCheckoutData] = useState(checkoutInitialData);
    const [step, selectStep, forwardStep] = useProgressiveSteps(STEP_CIVIL_STATE); // ce hook sauvegarde step et maxStep à chaque changement dans le localStorage, et récupère la valeur à l'initialisation

    //user from database
    const [user, loading, errors] = useFetch('/api/user/getCivilState');
    
    //récupération des données présentes dans localStorage ou dans user from database
    useEffect(() => {
        if(window.sessionStorage.getItem('checkout')) {                          
            setCheckoutData(
                Security.decryptToObject(window.sessionStorage.getItem('checkout'))
            );
        }
        else if(user) {
            const newStep = user.civility && user.firstName && user.lastName ? STEP_DELIVERY_ADDRESS: STEP_CIVIL_STATE;
            setCheckoutData(checkoutData => ({
                ...checkoutData,
                civilState: user,
                step: newStep
            }));
        };
    }, [user]);

    //à chaque changement de checkoutData on persiste les données dans localStorage
    useEffect(() => {
        if(checkoutData !== checkoutInitialData) {
            window.sessionStorage.setItem('checkout', Security.encryptFromObject(checkoutData));            
        }
    }, [checkoutData]);


    if(loading) {
        return <div>Chargement...</div>;
    }

    return (
        <div>
            <CivilState 
                edit={step === STEP_CIVIL_STATE} 
                civilState={checkoutData.civilState} 
                setCheckoutData={setCheckoutData} 
                selectStep={selectStep}
                forwardStep={forwardStep}
            />

            {
                step >= 2 && (

                    <DeliveryAddress 
                        edit={step === STEP_DELIVERY_ADDRESS} 
                        deliveryAddress={checkoutData.deliveryAddress} 
                        setCheckoutData={setCheckoutData} 
                        selectStep={selectStep}
                        forwardStep={forwardStep}
                    />
                )
            }

            {
                step >= 3 && (

                    <InvoiceAddress 
                        edit={step === STEP_INVOICE_ADDRESS} 
                        invoiceAddress={checkoutData.invoiceAddress} 
                        defaultAddress={checkoutData.deliveryAddress}
                        civilState={checkoutData.civilState} // car dans invoiceAddress le nom n'est pas modifiable, c'est celui de civilState
                        setCheckoutData={setCheckoutData} 
                        selectStep={selectStep}
                        forwardStep={forwardStep}
                    />
                )
            }

            {
                step >= 4 && (

                    <Payment checkoutData={checkoutData} cart={cart} />
                )
            }

        </div>
    )
}