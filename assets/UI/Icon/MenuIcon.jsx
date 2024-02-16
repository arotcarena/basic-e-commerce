import React from 'react';
import { Icon } from './Icon';

export const MenuIcon = ({additionalClass, ...props}) => {
    return <Icon additionalClass={`i-menu ${additionalClass}`} {...props} d="M120 816v-60h720v60H120Zm0-210v-60h720v60H120Zm0-210v-60h720v60H120Z" />
}