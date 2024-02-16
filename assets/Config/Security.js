import CryptoJS from 'crypto-js';

export class Security {

    static ENCRYPTION_KEY = 'ajkfYhUjhsuU';

    /**
     * 
     * @param {string} value 
     * @returns {string}
     */
    static encrypt(value) {
        return CryptoJS.AES.encrypt(value, Security.ENCRYPTION_KEY).toString();
    }
    
    /**
     * 
     * @param {string} value 
     * @returns {string} 
    */
   static decrypt(value) {
        return CryptoJS.AES.decrypt(value, Security.ENCRYPTION_KEY).toString(CryptoJS.enc.Utf8);
    }


    /**
     * 
     * @param {Object} value 
     * @returns {string}
     */
    static encryptFromObject(value) {
        return Security.encrypt(JSON.stringify(value));
    }

    /**
     * 
     * @param {string} value 
     * @returns {Object}
     */
    static decryptToObject(value) {
        return JSON.parse(Security.decrypt(value));
    }
}