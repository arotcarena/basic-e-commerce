import React, { useMemo, useState } from 'react';
import { STEP_DELIVERY_ADDRESS } from '..';
import { DeliveryAddressUpdate } from './DeliveryAddressUpdate.';
import { useCRUD } from '../../../../CustomHook/useCRUD';
import { DeliveryAddressCreate } from './DeliveryAddressCreate';
import { AddressItem } from './AddressItem';


export const DeliveryAddress = ({edit, deliveryAddress, setCheckoutData, selectStep, forwardStep}) => {

    const handleEdit = e => {
        e.preventDefault();
        selectStep(STEP_DELIVERY_ADDRESS);
    }
   
    if(!edit) {
        return (
            <div>
                <h3>Adresse de livraison</h3>
                <div>civilité : {deliveryAddress.civility} </div>
                <div>prénom : {deliveryAddress.firstName} </div>
                <div>Nom : {deliveryAddress.lastName}</div>
                <div>ligne 1 : {deliveryAddress.lineOne}</div>
                <div>ligne 2 : {deliveryAddress.lineTwo}</div>
                <div>code postal : {deliveryAddress.postcode}</div>
                <div>ville : {deliveryAddress.city}</div>
                <div>pays : {deliveryAddress.country}</div>
                <button onClick={handleEdit}>Editer</button>
            </div>
        )
    }


    const [addresses, updateAddress, createAddress, deleteAddress, loading, errors] = useCRUD('/api/address');
    const [state, setState] = useState({
        target: null,
        action: null
    });
    
    const handleSubmit = (formData) => {
        setCheckoutData(checkoutData => ({
            ...checkoutData,
            deliveryAddress: formData
        }));
        forwardStep(STEP_DELIVERY_ADDRESS);
    }

    const handleSelect = address => {
        handleSubmit(address);
    }

    const handleUpdate = address => {
        setState({
            action: 'update',
            target: address
        });
    };
    const handleCreate = e => {
        e.preventDefault();
        setState({
            action: 'create',
            target: null
        });
    };
    const handleCancel = e => {
        e.preventDefault();
        setState({
            action: null,
            target: null
        });
    };

    return (
        <div>
        <h3>Adresse de livraison</h3>
        {
            state.action === null && (
                <div>
                {
                    addresses.length > 0 
                    ?
                    <ul>
                        {
                            addresses.map(address => {
                                return (
                                    <AddressItem 
                                        key={address.id}
                                        address={address} 
                                        onSelect={handleSelect} 
                                        onUpdate={handleUpdate} 
                                        onDelete={deleteAddress} 
                                    />
                                )
                            })
                        }
                    </ul>
                    :
                    (
                        loading ? <div>Chargement...</div>: <div>Aucune adresse enregistrée</div>
                    )
                }
                    <button onClick={handleCreate}>Ajouter une adresse</button>
                </div>
            )
        }

        {
            state.action === 'create' && (
                <DeliveryAddressCreate mainSubmit={handleSubmit} create={createAddress} onCancel={handleCancel} />
            )
        }

        {
            state.action === 'update' && (
                <DeliveryAddressUpdate address={state.target} mainSubmit={handleSubmit} update={updateAddress} onCancel={handleCancel} />
            )
        }
            
        </div>
        
    )
}


