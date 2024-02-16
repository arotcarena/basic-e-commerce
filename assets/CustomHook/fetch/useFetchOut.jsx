import { useState } from 'react';
import { ApiError, addUrlParams, apiFetch } from '../../functions/api';

/**
 * SEND DATA / UPDATE DATABASE / NOT TO RECEIVE DATA
 * @param {string} entrypoint 
 * @param {string} method 
 * @returns 
 */
export const useFetchOut = (entrypoint, method = 'GET') => {

    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [info, setInfo] = useState(null);

    /**
     * 
     * @param {mixed} data
     * @param {null|CallableFunction} toDoWhenSuccess  fetch return value, is passed to this callable as argument
     */
    const sendData = async (data = null, toDoWhenSuccess = null) => {
        setLoading(true);
        setError(null);
        setInfo(null);

        let url = entrypoint;
        let options = {};
        
        if(method === 'GET' && data)  {
            url = addUrlParams(entrypoint, data); 
        }
        else if(method === 'POST') {
            options = {
                method: 'POST',
                headers: {
                    "Accept": "application/json",
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(data)
            };
        }

        try {
            const result = await apiFetch(url, options);
            setInfo(result);
            toDoWhenSuccess(result);
        } catch(e) {
            if(e instanceof ApiError) {
                setError(e.errors);
            } else {
                console.error(e);
            }
        }
        setLoading(false);
    };


    const resetError = () => {
        setError(null);
    }
    const resetInfo = () => {
        setInfo(null);
    }


    return {sendData, loading, error, info, resetError, resetInfo};
}

