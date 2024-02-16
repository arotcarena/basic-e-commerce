const reducer = (data, action) => {
    switch(action.type) {
        case 'FETCH':
            return action.payload;
        case 'CREATE':
            return [...data, action.payload];
        case 'UPDATE':
            return data.map(data => {
                if(data.id === action.target) {
                    return action.payload;
                }
                return data;
            });
        case 'DELETE':
            return data.filter(data => data.id !== action.target);
    }
}


// const [addresses, updateAddress, createAddress, deleteAddress, loading, errors] = useCRUD('/api/address');

import { useEffect, useReducer, useState } from "react";
import { ApiError, apiFetch } from "../functions/api";

export const useCRUD = (entrypoint) => {
    const [data, dispatch] = useReducer(reducer, []);
    const [loading, setLoading] = useState(false);
    const [errors, setErrors] = useState(null);

    useEffect(() => {
        (async () => {
            setLoading(true);
            try {
                const result = await apiFetch(entrypoint+'/index');
                dispatch({type: 'FETCH', payload: result});
            } catch(e) {
                if(e instanceof ApiError) {
                    setErrors(e.errors);
                } else {
                    console.error(e);
                }
            }
            setLoading(false);
        })();
    }, [entrypoint]);

    const create = async (data) => { 
        await apiFetch(entrypoint+'/create', {
            method: 'POST',
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(data)
        });
        dispatch({type: 'CREATE', payload: data});
        setLoading(false);
    };

    const update = async (data, id) => {
        setLoading(true);
        await apiFetch(entrypoint+'/'+id+'/update', {
            method: 'POST',
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(data)
        });
        dispatch({type: 'UPDATE', target: id, payload: data});
        setLoading(false);
    };

    const remove = async (id) => {
        setLoading(true);
        await apiFetch(entrypoint+'/delete', {
            method: 'POST',
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(id)
        });
        dispatch({type: 'DELETE', target: id});
        setLoading(false);
    };


    return [data, update, create, remove, loading, errors];
}