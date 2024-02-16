import { useEffect, useState } from 'react';
import { apiFetch } from '../../functions/api';

export const useFetchWithDelay = (entrypoint, data, method = 'GET', delay = 300) => {

    const [loading, setLoading] = useState(false);
    const [result, setResult] = useState(null);
    const [error, setError] = useState(null);
    const [timer, setTimer] = useState(null);


    useEffect(() => {
        if(timer) {
            clearTimeout(timer);
            setTimer(null);
        }
        setLoading(true);
        const currentTimer = setTimeout(async () => {
            let url = entrypoint;
            let options = {};
            if(method === 'GET') {
                let params = [];
                for(const [key, value] of Object.entries(data)) {
                    params.push(key+'='+value);
                }
                if(params.length > 0) {
                    url += '?' + params.join('&');
                }
            }
            if(method === 'POST') {
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
                setResult(result);
            } catch(e) {
                setError(e);
            }
            setLoading(false);
            setTimer(null);
        }, delay);
        setTimer(currentTimer);

    }, [entrypoint, method, data]);


    const reset = () => {
        setResult(null);
        setLoading(false);
    }

    return [result, loading, error, reset];
}
