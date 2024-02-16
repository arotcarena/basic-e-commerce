import React, { useRef, useState } from 'react';
import { ExpandMenu } from '../../../../UI/Container/ExpandMenu';
import { HeaderLogo } from '../../../../UI/Logo/HeaderLogo';
import { ProductSuggest } from './ProductSuggest';
import { CloseButton } from '../../../../UI/Button/CloseButton';
import { useFetchQSearch } from '../../../../CustomHook/fetch/useFetchQSearch';
import '../../../../styles/Header/HeaderBottom/searchTool.css';

export const SearchTool = ({close}) => {
    
    //handle q change
    const [q, setQ] = useState('');
    const handleChange = e => {
        setQ(e.currentTarget.value);
    }
    
    //fetch products & count
    const [result, resetProducts, loading, error] = useFetchQSearch('/api/product/search', q);
    
    //close expand
    const inputRef = useRef(null);
    const handleCloseExpand = () => {
        resetProducts();
        setQ('');
        inputRef.current.focus();
    }

    return (
        <div className="search-tool-header">
            <CloseButton additionalClass="search-tool-closer" onClick={close} />
            <HeaderLogo />
            <form className="search-tool-group" action="/recherche" method="GET">
                <input name="q" ref={inputRef} className="search-tool-input" type="text" placeholder="Rechercher" value={q} onChange={handleChange} autoFocus={true} />
            </form>
            {
                (loading || result?.products.length > 0) && (
                    <ExpandMenu onClose={handleCloseExpand}>
                    {
                        result?.products.length > 0
                        ?
                        (
                            <>
                                <nav className="header-bottom-subnav">
                                {
                                    result.products.map(product => <ProductSuggest key={product.id} product={product} q={q} />)
                                }
                                </nav>
                                <a className="link" href={'/recherche?q='+q}>Voir tous les résultats {result?.count ? `(${result.count})`: ''}</a>
                            </>
                        )
                        :
                        <div className="expand-loading">Chargement...</div>
                    }
                    </ExpandMenu>
                )
            }
        </div>
    )
}


