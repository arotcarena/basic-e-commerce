
import React from 'react';
import { createRoot } from 'react-dom/client';
import { Header } from './Components/Header';



const header = document.getElementById('header');
const headerRoot = createRoot(header);
const viewSearchButton = document.getElementById('product-index') ? false: true;

headerRoot.render(
    <Header 
        categories={JSON.parse(header.dataset.categories)} 
        activeCategory={header.dataset.activecategory} 
        activeSubCategory={header.dataset.activesubcategory}
        viewSearchButton={viewSearchButton}
        />
);



