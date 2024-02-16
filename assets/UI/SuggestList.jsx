import React, { useEffect, useState } from 'react';

export const SuggestList = ({additionalClass, suggests, onSelect, onClose, render}) => {
    //fermeture au click sur le côté
    useEffect(() => {
            document.addEventListener('click', onClose);
            return () => document.removeEventListener('click', onClose);
        }, []);

    
    //utilisation au clavier
    const [selected, setSelected] = useState(null);

    useEffect(() => {
        const onKeyDown = e => {
            switch(e.key) {
                case 'ArrowUp':
                    e.preventDefault();
                    setSelected(selected => {
                        if(selected === null || selected <= 0) {
                            return (suggests.length - 1);
                        }
                        return (selected - 1);
                    });
                    break;
                case 'ArrowDown':
                    e.preventDefault();
                    setSelected(selected => {
                        if(selected === null || selected >= (suggests.length - 1)) {
                            return 0;
                        }
                        return (selected + 1);
                    });
                    break;
                default: 
                    break;
            }
        }
        document.addEventListener('keydown', onKeyDown);
        return () => document.removeEventListener('keydown', onKeyDown);
    }, []);

    useEffect(() => {
        const onKeyDown = e => {
           if(e.key === 'Enter') {
                e.preventDefault();
                onSelect(suggests[selected]);
           }
        }
        document.addEventListener('keydown', onKeyDown);
        return () => document.removeEventListener('keydown', onKeyDown);
    }, [selected]);
    
    return (
        
        <ul className={`suggest-list ${additionalClass}`} onClick={e => e.stopPropagation()} role="listbox" aria-label="Suggestions">
            {
                suggests.map((suggest, index) => render(suggest, selected === index))
            }
        </ul>
    )
}



    
