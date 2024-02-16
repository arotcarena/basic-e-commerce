import React from 'react';
import { Icon } from './Icon';

export const ChevronRightIcon = ({additionalClass, ...props}) => {
    return <Icon additionalClass={`i-chevron-right ${additionalClass}`} {...props} d="m375 816-43-43 198-198-198-198 43-43 241 241-241 241Z" />
}