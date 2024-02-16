import React from 'react';
import { MenuButton } from '../../../../UI/Button/MenuButton';
import { useOpenState } from '../../../../CustomHook/useOpenState';
import { Modal } from '../../../../UI/Container/Modal';
import { MobileMenu } from './MobileMenu';

export const MobileMenuControl = ({categories}) => {

    const [sideMenuIsOpen, openSideMenu, closeSideMenu] = useOpenState();

    return (
        <>
            <MenuButton additionalClass="mobile-menu-opener" onClick={openSideMenu} />
            <Modal isOpen={sideMenuIsOpen} close={closeSideMenu} additionalClass="left side-menu">
                <MobileMenu categories={categories} close={closeSideMenu} />
            </Modal>
        </>
    )
}