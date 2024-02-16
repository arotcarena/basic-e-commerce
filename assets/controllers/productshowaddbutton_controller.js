import { Controller } from '@hotwired/stimulus';
import { ApiError, apiFetch } from '../functions/api';
import { cartChipAdd } from '../functions/dom';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="hello" attribute will cause
 * this controller to be executed. The name "hello" comes from the filename:
 * hello_controller.js -> "hello"
 *
 * Delete this file or adapt it for your use!
 */
export default class extends Controller {

    #errorTimeout = 2000;

    connect() {
        this.element.addEventListener('click', this.#handleClick.bind(this));
    }

    async #handleClick(e) {
        e.preventDefault();
        try {
            this.element.innerText = "Ajout en cours...";
            await apiFetch('/api/cart/add/id-'+this.element.dataset.productid+'_quantity-'+this.element.dataset.quantitytoadd);
            cartChipAdd(parseInt(this.element.dataset.quantitytoadd), parseInt(this.element.dataset.productprice));
        } catch(e) {
            const error = document.createElement('div');
            error.classList.add('form-error');
            if(e instanceof ApiError) {
                error.innerText = e.errors;
            } else {
                throw e;
            }
            this.element.parentElement.append(error);
            setTimeout(() => {
                error.remove();
            }, this.#errorTimeout);
            this.element.innerText = "Ajouter au panier";
            throw e;
        }
        this.element.innerText = "Ajouter au panier";
    }   
}
