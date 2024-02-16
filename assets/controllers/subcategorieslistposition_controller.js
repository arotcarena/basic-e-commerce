import { Controller } from '@hotwired/stimulus';
import { apiFetch } from '../functions/api';

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

    /** @type {HTMLElement} */
    #loader;

    /** @type {HTMLElement} */
    #list;

    connect() {
        const parentCategorySelect = this.element.querySelector('#sub_category_parentCategory');
        
        parentCategorySelect.addEventListener('change', this.#onChange.bind(this));
        
        this.#loader = document.createElement('div');
        this.#loader.classList.add('admin-form-info');
        this.#loader.innerText = 'Chargement...';
        
        this.#list = document.createElement('ul');
        this.#list.classList.add('admin-form-subCategory-list');

        this.#updateList(parentCategorySelect.value);
    }

    #onChange(e) {
        this.#list.innerText = '';
        this.#list.remove();
        this.#updateList(e.currentTarget.value)
    }

    async #updateList(categoryId) {
        this.element.append(this.#loader);
        const category = await apiFetch('/admin/api/category/'+categoryId);
        this.#renderList(category);
        this.#loader.remove();
    }

    /**
     * 
     * @param {array} category 
     */
    #renderList(category)
    {
        let maxPosition = 0;
        let currentPosition = null;

        this.#list.innerHTML = '<div>Sous-catégories :</div>';
        for(const subCategory of category.subCategories) {
            // on récupère la position max parmis les subCategories existantes pour placer une valeur supérieure par défaut dans le champ listPosition 
            // dans le cas ou il s'agit de la catégorie dont on fait déjà partie, on remet le même listPosition
            if(subCategory.listPosition > maxPosition) {
                maxPosition = subCategory.listPosition;
            }
            const li = document.createElement('li');
            li.classList.add('admin-form-subCategory-item');
            li.innerText = subCategory.listPosition + '. ' + subCategory.name;
            this.#list.append(li);
            if(this.element.dataset?.subcategoryid === subCategory.id.toString()) {
                li.classList.add('current');
                currentPosition = subCategory.listPosition;
            }
        }
        this.element.append(this.#list);
        /* on place par défaut une listPosition supérieure aux existantes */
        this.element.nextElementSibling.querySelector('#sub_category_listPosition').value = currentPosition ? currentPosition: maxPosition + 1;
    }
}
