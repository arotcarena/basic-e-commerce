class PriceTransformer {
    transform(price) {
        if(price === '') {
            return '';
        }
        return price / 100;
    }
    reverseTransform(price) {
        if(price === '') {
            return '';
        }
        return price * 100;
    }
}

export const priceTransformer = new PriceTransformer();