import { apiFetch } from "./api";
import { priceFormater } from "./formaters";

/**
 * Appelé lors d'une modification du panier
 * 
 * @param {number|null} count 
 * @param {number|null} totalPrice 
 */
export const cartChipUpdate = async (count = null, totalPrice = null) => {

    // si rien n'est passé, on initialise les valeurs avec un appel au serveur
    if(count === null || totalPrice === null) {
        const result = await apiFetch('/api/cart/getLightCart');
        count = result.count;
        totalPrice = result.totalPrice;
    }

    // on affiche les valeurs
    if(document.querySelector('.cart-chip')) {

        const cartChip = document.querySelector('.cart-chip');
        setContent(cartChip, count, totalPrice);
        
        if(count === 0) {
            cartChip.setAttribute('hidden', true);
        } else {
            setVisible(cartChip);
        }
        
    }
}


/**
 * Appelé uniquement quand on ajoute un produit depuis product_index ou product_show (pas lors d'un ajout de quantité depuis le panier)
 * @param {number} quantityToAdd 
 * @param {number} productPrice 
 */
export const cartChipAdd = (quantityToAdd, productPrice) => {
    if(document.querySelector('.cart-chip')) {

        const cartChip = document.querySelector('.cart-chip');
        
        const count = parseInt(cartChip.dataset.count) + quantityToAdd;
        const price = parseInt(cartChip.dataset.price) + (quantityToAdd * productPrice);

        setContent(cartChip, count, price);

        if(count > 0) {
            setVisible(cartChip);
        }
    }
}





/**
 * 
 * @param {HTMLElement} cartChip 
 * @param {number} count 
 * @param {number} price 
 */
const setContent = (cartChip, count, price) => {
    cartChip.dataset.count = count;
    cartChip.dataset.price = price;

    cartChip.querySelector('.cart-count-chip').innerText = count.toString();
    cartChip.querySelector('.cart-price-chip').innerText = priceFormater(price);
}

/**
 * 
 * @param {HTMLElement} cartChip 
 */
const setVisible = (cartChip) => {
    cartChip.removeAttribute('hidden');
    cartChip.classList.add('change');
    setTimeout(() => {
        cartChip.classList.remove('change');
    }, 10);
}