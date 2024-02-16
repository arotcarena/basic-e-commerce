import React, { useEffect, useState } from 'react';
import { useFetchQSearch } from '../../../CustomHook/fetch/useFetchQSearch';
import { AdminProductSuggest } from './AdminProductSuggest';
import { SuggestedProductsList } from './SuggestedProductsList';
import { apiFetch } from '../../../functions/api';
import { SuggestList } from '../../../UI/SuggestList';
import { CloseButton } from '../../../UI/Button/CloseButton';
import '/assets/styles/Admin/SuggestedProductsInput/index.css';

export const SuggestedProductsInput = ({productId}) => {

    //gestion de l'affichage des suggestedProducts
    const [suggestedProducts, setSuggestedProducts] = useState([]);
    const [listLoading, setListLoading] = useState(false);
    useEffect(() => {
        if(productId) {
            (async () => {
                setListLoading(true);
                try {
                    const data = await apiFetch('/admin/api/product/'+productId+'/suggestedProducts');
                    setSuggestedProducts(data);
                } catch(e) {
                    //
                }
                setListLoading(false);
            })();
        }
    }, []);
    const handleRemove = (product) => {
        setSuggestedProducts(suggestedProducts => suggestedProducts.filter(p => p.id !== product.id));
    };

    //gestion du input
    const [q, setQ] = useState('');
    const handleChange = e => {
            setQ(e.currentTarget.value);
    };

    //search products
    const [result, resetProducts, loading, error] = useFetchQSearch('/api/product/search', q);
    const handleSelect = (product) => {
        setSuggestedProducts(suggestedProducts => {
            //on vérifie si le produit a déjà été sélectionné
            let included = false;
            for(const suggestedProduct of suggestedProducts) {
                if(suggestedProduct.id === product.id) {
                    included = true;
                }
            }
            if(!included) {
                return [
                    ...suggestedProducts,
                    product
                ];
            }
            return suggestedProducts;
        });
        resetProducts();
        setQ('');
    }

    return (
        <>
            <div className="admin-form-group suggestedProducts-group">
                <label htmlFor="product_suggestedProducts">Produits à suggérer</label>
                <SuggestedProductsList products={suggestedProducts} onRemove={handleRemove} loading={listLoading} />
                <input type="text" id="product_suggestedProducts" className="admin-form-control" placeholder="Rechercher un produit" name="q" value={q} onChange={handleChange} />
                {
                    q !== '' && <CloseButton additionalClass="admin-suggestedProducts-input-closer" onClick={() => setQ('')} />
                }
                {
                    result?.products.length > 0 && (
                        <SuggestList 
                                    additionalClass="admin-suggest-list" 
                                    suggests={result.products} 
                                    onClose={resetProducts} 
                                    onSelect={handleSelect}
                                    render={(product, selected) => <AdminProductSuggest key={product.id} product={product} q={q} selected={selected} onSelect={handleSelect} />} 
                        />   
                    )
                }
            </div>
            {
                loading && <div className="admin-form-info">Chargement...</div>
            }


            <select hidden={true} name="product[suggestedProducts][]" value={suggestedProducts.map(product => product.id)} onChange={() => {}} multiple="multiple">
            {
                suggestedProducts.map(product => <option className="suggestedProducts-hidden-option" key={product.id} value={product.id} data-designation={product.designation}>{product.designation}</option>)  //data-designation : pour les tests endtoend (car on peut accéder seulement au text visible et comme il est hidden)
            }
            </select>
        </>

        
    )
}






