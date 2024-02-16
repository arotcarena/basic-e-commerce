export const apiFetch = async (entrypoint, options = {}) => {
    const response = await fetch(entrypoint, {
        method: 'GET',
        headers: {
            "Accept": "application/json",
            "Content-Type": "application/json"
        },
        ...options
    });
    const data = await response.json();
    if(response.ok) {
        return data;
    }
    if(data.errors) {
        throw new ApiError(data.errors);
    }
    throw new Error('fetch error');
}

export class ApiError {
    errors;

    constructor(errors) {
        this.errors = errors;
    }
}




/**
 * 
 * @param {string} entrypoint 
 * @param {Object} data {key: value, key: value...} 
 * @returns {string} readyUrl
 */
export const addUrlParams = (entrypoint, data) => {
    const params = [];
    for(const [key, value] of Object.entries(data)) {
        params.push(`${key}=${value}`);
    }
    return entrypoint + '?' + params.join('&');
}
