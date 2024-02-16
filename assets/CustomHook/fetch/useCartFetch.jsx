import { useCallback, useReducer, useState } from "react"
import { ApiError, apiFetch } from "../../functions/api";


const reducer = (cart, action) => {
    switch(action.type) {
        case 'START_LOADING':
            return {
                ...cart,
                generalLoading: true
            };
        case 'STOP_LOADING':
            return {
                ...cart,
                generalLoading: false
            };
        case 'FETCH':
            return {
                ...cart,
                // action.payload === fullCart
                // on transforme les cartlines en lines
                lines: action.payload.cartLines.map(cartLine => ({
                    product: cartLine.product,
                    quantity: cartLine.quantity,
                    totalPrice: cartLine.totalPrice,
                    error: null
                })),
                count: action.payload.count,
                totalPrice: action.payload.totalPrice,
                generalError: null
            };
        case 'ADD':
            return {
                ...cart,
                lines: cart.lines.map(line => {
                    if(line.product.id === action.target) {
                        //avant d'ajouter on vérifie le stock
                        //action.payload représente la quantité voulue, et pas la quantité d'ajout (évite les bugs si on clique plein de fois d'affilée)
                        if(action.payload > line.product.stock) {
                            line.quantity = line.product.stock;
                            line.error = 'Stock insuffisant';
                        } else {
                            line.quantity = action.payload;
                            line.error = null;
                        }
                        line.totalPrice = line.product.price * line.quantity;
                    }
                    return line;
                })
            };
        case 'LESS':
            return {
                ...cart,
                lines: cart.lines.map(line => {
                    if(line.product.id === action.target) {
                        //avant d'enlever on vérifie si < 1
                        //action.payload représente la quantité voulue, et pas la quantité d'ajout (évite les bugs si on clique plein de fois d'affilée)
                        if(action.payload < 1) {
                            line.quantity = 1;
                            line.error = 'Quantité minimum';
                        } else {
                            line.quantity = action.payload;
                            line.error = null;
                        }
                        line.totalPrice = line.product.price * line.quantity;
                    }
                    return line;
                }),
            };
        case 'REMOVE':
            const lineToRemove = cart.lines.find(line => line.product.id === action.target);
            const lineTotalPrice = lineToRemove.totalPrice;
            const lineQuantity = lineToRemove.quantity;
            return {
                ...cart,
                lines: cart.lines.filter(line => line.product.id !== action.target),
                totalPrice: cart.totalPrice - lineTotalPrice,
                count: cart.count - lineQuantity
            };
        case 'ERROR':
            return {
                ...cart,
                generalLoading: false,
                generalError: action.payload 
            }
        case 'UPDATE_TOTAL_PRICE_AND_COUNT':
            const totalPrice = cart.lines.reduce((acc, line) => {
                return acc + (line.product.price * line.quantity);
            }, 0);
            const count = cart.lines.reduce((acc, line) => {
                return acc + line.quantity;
            }, 0);
            
            return {
                ...cart,
                totalPrice: totalPrice,
                count: count
            };
    }
}



export const useCartFetch = () => {

    const [cart, dispatch] = useReducer(reducer, {
        lines: [],
        count: null,
        totalPrice: null,
        generalLoading: false,
        generalError: null
    });


    const fetchCart = useCallback(async () => {
        dispatch({type: 'START_LOADING'});
        try {
            const fullCart = await apiFetch('/api/cart/getFullCart');
            dispatch({type: 'FETCH', payload: fullCart});
        } catch(e) {
            dispatch({type: 'ERROR', payload: e});
        }
        dispatch({type: 'STOP_LOADING'});
    }, []);

    const remove = useCallback(async (productId) => {
        dispatch({type: 'REMOVE', target: productId});
        try {
            await apiFetch('/api/cart/remove/id-'+productId);
        } catch(e) {
            //on réactualise pour être raccord avec la database
            fetchCart();
        }
    }, [fetchCart]);

    const add = useCallback(async (productId, quantity, neededQuantity) => {
        dispatch({type: 'ADD', target: productId, payload: neededQuantity}); 
        //on doit attendre la mise à jour de l'état ci-dessus pour savoir si l'ajout a pu se faire, et ensuite mettre à jour le prix total et le count
        dispatch({type: 'UPDATE_TOTAL_PRICE_AND_COUNT'});
        try {
            await apiFetch('/api/cart/add/id-'+productId+'_quantity-'+quantity);
        } catch(e) {
            if(!e instanceof ApiError) {
                //en cas d'erreur autre que stock (gérée en local) on réactualise pour être raccord avec la database
                fetchCart();
            }
        }
    }, [fetchCart]);

    const less = useCallback(async (productId, quantity, neededQuantity) => {
        dispatch({type: 'LESS', target: productId, payload: neededQuantity});  
        //on doit attendre la mise à jour de l'état ci-dessus pour savoir si la soustraction a pu se faire, et ensuite mettre à jour le prix total et le count
        dispatch({type: 'UPDATE_TOTAL_PRICE_AND_COUNT'});
        try {
            await apiFetch('/api/cart/less/id-'+productId+'_quantity-'+quantity);
        } catch(e) {
            if(!e instanceof ApiError) {
                //en cas d'erreur autre que notEnough (gérée en local) on réactualise pour être raccord avec la database
                fetchCart();
            }
        }
    }, [fetchCart]);


    return {cart, fetchCart, remove, add, less};
}





