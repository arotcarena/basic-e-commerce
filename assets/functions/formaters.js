export const priceFormater = (price) => {
    const formater = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' });
    return formater.format(price / 100);
};