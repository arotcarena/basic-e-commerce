import { Controller } from '@hotwired/stimulus';
import { apiFetch } from '../functions/api';


export default class extends Controller {

    
    /** @type {HTMLElement}  */
    #categorySelect;

    /** @type {HTMLElement}  */
    #subCategorySelect;

    /** @type {HTMLElement} */
    #loader;

    connect() {
        this.#categorySelect = this.element.querySelector('#product_category');
        this.#subCategorySelect = this.element.querySelector('#product_subCategory');
        
        this.#categorySelect.addEventListener('change', this.#onCategoryChange.bind(this));
        
        this.#loader = document.createElement('div');
        this.#loader.classList.add('admin-form-info');
        this.#loader.innerText = 'Chargement...';

        this.#handleSubCategories(
            this.#categorySelect.querySelector('option[selected=selected]')?.value
        );
    }

    async #onCategoryChange(e) {
        this.#subCategorySelect.value = '';
        this.#handleSubCategories(e.currentTarget.value);   
    }
    
    /**
     * 
     * @param {undefined|number} categoryId 
     */
    async #handleSubCategories(categoryId) {
        this.#subCategorySelect.parentElement.style.display = 'none';
        this.#categorySelect.parentElement.append(this.#loader);
        if(categoryId !== undefined) {

            try {
                const subCategoryIds = await apiFetch('/admin/api/category/'+categoryId+'/subCategory_ids');
                this.#subCategorySelect.querySelectorAll('option').forEach(option => {
                    if(!subCategoryIds.includes(parseInt(option.value))) {
                        if(option.getAttribute('value')) {
                            option.setAttribute('hidden', true);
                        }
                    } else {
                        option.removeAttribute('hidden');
                    }
                });
                this.#subCategorySelect.parentElement.style.display = 'block';
            } catch(e) {
                //
            }
        }
        this.#loader.remove();
    }
}
