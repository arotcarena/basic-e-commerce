import React from 'react';
import { Icon } from './Icon';

export const ExpandMoreIcon = ({additionalClass, ...props}) => {
    return <Icon additionalClass={`i-expand-more ${additionalClass}`} {...props} d="M480 711 240 471l43-43 197 198 197-197 43 43-240 239Z" />
}


