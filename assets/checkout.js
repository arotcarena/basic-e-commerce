
import './styles/app.css';


import React from 'react';
import { createRoot } from 'react-dom/client';
import { Checkout } from './Components/Checkout';



const checkout = document.getElementById('checkout');
const checkoutRoot = createRoot(checkout);
checkoutRoot.render(<Checkout />);