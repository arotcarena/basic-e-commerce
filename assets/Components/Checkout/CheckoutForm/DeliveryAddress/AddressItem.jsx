import React from 'react';

export const AddressItem = ({address, onSelect, onUpdate, onDelete}) => {

    const handleSelect = e => {
        e.preventDefault();
        onSelect(address);
    }
    const handleUpdate = e => {
        e.preventDefault();
        onUpdate(address);
    }
    const handleDelete = e => {
        e.preventDefault();
        onDelete(address.id);
    }

    return (
        <li>
            <div>civilité : {address.civility} </div>
            <div>prénom : {address.firstName} </div>
            <div>Nom : {address.lastName}</div>
            <div>ligne 1 : {address.lineOne}</div>
            <div>ligne 2 : {address.lineTwo}</div>
            <div>code postal : {address.postcode}</div>
            <div>ville : {address.city}</div>
            <div>pays : {address.country}</div>
            <button onClick={handleSelect}>Choisir</button>
            <button onClick={handleUpdate}>Modifier</button>
            <button onClick={handleDelete}>Supprimer</button>
        </li>
    )
}