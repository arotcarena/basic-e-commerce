import { useState } from "react";
import { ApiError, apiFetch } from "../../functions/api";

export const useControlledFetch = (errorTimeout = null) => {
    const [data, setData] = useState(null);
    const [error, setError] = useState(null);
    const [loading, setLoading] = useState(false);

    const doFetch = async (entrypoint, options = {}) => {
        setError(null);
        setLoading(true);
        try {
            const result = await apiFetch(entrypoint, options);
            setData(result);
        } catch(e) {
            if(e instanceof ApiError) {
                setError(e.errors);
            } else {
                setError(e);
            }
            if(errorTimeout) {
                setTimeout(() => {
                    setError(null);
                }, errorTimeout);
            }
            setLoading(false);
            throw e;
        }
        setLoading(false);
    };

    return [doFetch, data, loading, error];
}