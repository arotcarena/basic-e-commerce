import React, { useState } from 'react';
import { Filters } from './Filters';
import { ProductCard } from './ProductCard';
import { useFetchWithDelay } from '../../../CustomHook/fetch/useFetchWithDelay';
import '../../../styles/Shop/ProductIndex/index.css';

export const ProductIndex = ({search = '', categoryId = null, subCategoryId = null}) => {

    const [filters, setFilters] = useState({
        q: search.split('_').join(' '),
        minPrice: '',
        maxPrice: '',
        sort: 'createdAt_DESC',
        categoryId: categoryId,
        subCategoryId: subCategoryId
    });

    //quand on ajoutera pagination, il faudra faire merge({offset: ..., limit: ...}, filters)
    const [result, loading, error] = useFetchWithDelay('/api/product/index', filters, 'GET', 300);


    return (
        <div className="container">
            {
                search !== '' && <h1>Résultats pour "{filters.q}"</h1>
            }
            <Filters filters={filters} setFilters={setFilters} /> 
            {
                result && (
                    <>
                        <p>{result.count} résultats</p>
                        <ul className="product-list">
                        {
                            result.products.map(product => <ProductCard key={product.id} product={product} />)
                        }
                        </ul>
                    </>
                )
            }
        </div>
    )
}






