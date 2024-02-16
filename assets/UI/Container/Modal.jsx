import React, { useRef } from 'react';
import { CloseButton } from '../Button/CloseButton';
import { createPortal } from 'react-dom';
import '/assets/styles/UI/Container/modal.css';

export const Modal = ({children, additionalClass, isOpen, close, animated = true}) => {

    const containerRef = useRef();

    const handleClose = () => {
        if(!animated) {
            close();
            return;
        }
        containerRef.current.classList.add('close');
        containerRef.current.addEventListener('animationend', () => {
            close();
        });
    };

    const handleStopPropagation = e => {
        e.stopPropagation();
    };

    return (
            isOpen && createPortal(
                <div ref={containerRef} className={`modal-container ${additionalClass}-container`} onClick={handleClose}>
                    <div className={`modal ${additionalClass}`} onClick={handleStopPropagation}>
                        <CloseButton additionalClass="modal-closer" onClick={handleClose} />
                        {children}
                    </div>
                </div>,
                document.body
            )
    )
}



