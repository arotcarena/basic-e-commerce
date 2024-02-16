import { useEffect, useState } from 'react';

export const AutoDestroy = ({children, delay}) => {
    
    const [alive, setAlive] = useState(true);

    useEffect(() => {
        setTimeout(() => {
            setAlive(false);
        }, delay);
    }, []);

    return alive && children;
}