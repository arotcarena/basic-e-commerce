import React from 'react';
import { Icon } from './Icon';

export const CloseIcon = ({additionalClass, ...props}) => {
    return <Icon additionalClass={`i-close ${additionalClass}`} {...props} d="m249 849-42-42 231-231-231-231 42-42 231 231 231-231 42 42-231 231 231 231-42 42-231-231-231 231Z" />
}
