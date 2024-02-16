
import './styles/app.css';


import React from 'react';
import { createRoot } from 'react-dom/client';
import { ProductIndex } from './Components/Shop/ProductIndex';



const productIndex = document.getElementById('product-index');
const productIndexRoot = createRoot(productIndex);
productIndexRoot.render(
    <ProductIndex 
        search={productIndex.dataset?.search} 
        categoryId={productIndex.dataset?.categoryid} 
        subCategoryId={productIndex.dataset?.subcategoryid} 
        />
);