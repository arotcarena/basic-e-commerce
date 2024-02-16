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

    #width;

    #height;

    #src;

    connect() {
        this.#width = this.element.offsetWidth;
        this.#height = this.element.offsetHeight;
        this.#src = this.element.querySelector('img').getAttribute('src');

        this.element.addEventListener('mouseover', this.#onMouseOver.bind(this));
        this.element.addEventListener('mousemove', this.#onMouseMove.bind(this));
        this.element.addEventListener('mouseleave', this.#onMouseLeave.bind(this));
    }

    #onMouseOver() {
        this.element.style.width = this.#width + 'px';
        this.element.style.height = this.#height + 'px';
        this.element.querySelector('img').style.display = 'none';
    }

    #onMouseMove(e) {
        const x = Math.round(e.offsetX * 100 / this.#width);
        const y = Math.round(e.offsetY * 100 / this.#height);

        this.element.style.background = 'url('+this.#src+') '+ x +'% '+ y +'% / 300% no-repeat';
    }
    #onMouseLeave() {
        this.element.querySelector('img').removeAttribute('style');
        this.element.removeAttribute('style');
    }
}
