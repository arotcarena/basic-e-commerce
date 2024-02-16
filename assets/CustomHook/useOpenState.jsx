import { useState } from "react";

export const useOpenState = (startingState = false) => {
    const [isOpen, setOpen] = useState(startingState);
    const open = () => {
        setOpen(true);
    };
    const close = () => {
        setOpen(false);
    };
    return [isOpen, open, close];
}

