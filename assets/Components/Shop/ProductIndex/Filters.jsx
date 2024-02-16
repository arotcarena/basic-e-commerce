import React from 'react';
import { TextFieldWithTransform } from '../../../UI/Form/TextField';
import { Option, Select } from '../../../UI/Form/Select';
import '../../../styles/Shop/ProductIndex/filters.css';
import { priceTransformer } from '../../../Service/DataTransformer/PriceTransformer';

export const Filters = ({filters, setFilters}) => {

    const handleChange = (name, value) => {
        setFilters(filters => ({
            ...filters,
            [name]: value
        }));
    };

    return (
        <div className="search-filters">
            <input type="text" className="searchbar" name="q" value={filters.q} onChange={handleChange} placeholder="Rechercher" />
            <TextFieldWithTransform type="number" name="minPrice" transformer={priceTransformer} value={filters.minPrice} onChange={handleChange}>Prix min</TextFieldWithTransform>
            <TextFieldWithTransform type="number" name="maxPrice" transformer={priceTransformer} value={filters.maxPrice} onChange={handleChange}>Prix max</TextFieldWithTransform>
            <Select name="sort" value={filters.sort} onChange={handleChange}>
                <Option value="createdAt_DESC">Plus récents d'abord</Option>
                <Option value="createdAt_ASC">Plus anciens d'abord</Option>
                <Option value="price_ASC">Moins cher d'abord</Option>
                <Option value="price_DESC">Plus cher d'abord</Option>
            </Select>
        </div>
    )
}