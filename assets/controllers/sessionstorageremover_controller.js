import { Controller } from '@hotwired/stimulus';

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

    connect() {
        const items = this.element.dataset.items;
        if(items) {
            for(const item of JSON.parse(items)) {
                window.sessionStorage.removeItem(item);
            }
        }
    }

}
