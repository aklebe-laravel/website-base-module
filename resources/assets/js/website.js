import {Form} from "../../../../Form/resources/assets/js/form";
import {messageBox} from "../../../../Form/resources/assets/js/messageBox";
import {user} from "../../../../Form/resources/assets/js/user";

export class Website {

    /**
     *
     * @type {boolean}
     */
    isLoaded = false;

    /**
     *
     * @type {{hamburgerOpen: boolean}}
     */
    navigation = {
        hamburgerOpen: false,
    };

    /**
     *
     */
    form = null;

    /**
     *
     */
    messageBox = messageBox();

    /**
     * Represents a user.
     * It's not the current user! Can be changed multiple times.
     * Will be used for cross selling for example.
     */
    user = user();

    /**
     * Use this to overwrite and calling parent by super.start()
     */
    startWebsite = function (classInstance) {
        classInstance.user.start(classInstance);
        classInstance.messageBox.start(classInstance);

        // replace hover titles with nice popovers
        classInstance.callOnReady(() => {
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl)
            })
        });
    };

    callOnReady = function (callback) {
        if (document.readyState !== 'loading') {
            callback();
        } else {
            // @todo: not sure 'alpine:init' should have checked on this place
            ['DOMContentLoaded', 'alpine:init'].forEach(evt => document.addEventListener(evt, () => {
            // ['DOMContentLoaded'].forEach(evt => document.addEventListener(evt, () => {
                callback();
            }));
        }
    };

    /**
     *
     * @param url
     * @returns {Promise<any>}
     */
    requestGet = function (url) {
        return fetch(url).then(res => res.json() ?? {})
    };

    /**
     *
     * @param url
     * @param data
     * @returns {Promise<any>}
     */
    requestPost = function (url, data) {
        return fetch(url, {
            method: 'POST', headers: {
                'Content-Type': 'application/json', // 'Content-Type': 'multipart/form-data',
                'X-CSRF-TOKEN': document.head.querySelector('meta[name=csrf-token]').content
            }, body: JSON.stringify(data)
        }).then(res => res.json());
    };

    /**
     *
     */
    start = function () {
        let classInstance = this;
        classInstance.startWebsite(classInstance);
    };

    /**
     *
     * @param el
     */
    scrollTo = function (el) {
        let elm = document.querySelector(el);
        window.scrollTo(elm.offsetLeft, elm.offsetTop);
    };

    /**
     *
     */
    scrollToForm = function () {
        let classInstance = this;
        classInstance.scrollTo('.website-base-dt-forms');
    };

    /**
     *
     * @param t
     * @param path
     * @param defaultResult
     * @returns {string}
     */
    getValue = function (t, path, defaultResult) {
        let result = path.split(".").reduce((r, k) => r?.[k], t);
        if (!result) {
            // result = '[???]';
            // result = path;
            result = defaultResult ?? '';
        }
        return result;
    };

    /**
     *
     * @param key
     * @param replace
     * @returns {*}
     */
    trans = function (key, replace = {}) {
        let classInstance = this;
        var translation = classInstance.getValue(window.translations[window.currentLocale], key);
        // var translation = key.split('.').reduce((t, i) => t[i] || null, window.translations[window.currentLocale]);

        if (!translation) {
            return key;
        }

        for (var placeholder in replace) {
            translation = translation.replace(`:${placeholder}`, replace[placeholder]);
        }
        return translation;
    };

    /**
     *
     * @param modelName
     * @returns {Form}
     */
    getNewForm = function (modelName) {
        return new Form(modelName);
    }

    /**
     *
     * @returns {string}
     */
    getViewport = function (el) {
        let width = Math.max(document.documentElement.clientWidth, window.innerWidth || 0)

        if (typeof el !== "undefined") {
            width = el.offsetWidth;
        }

        // xs - size less than or 575.98
        if (width <= 575.98) return 'xs'
        // sm - size between 576 - 767.98
        if (width >= 576 && width <= 767.98) return 'sm'
        // md - size between 768 - 991.98
        if (width >= 768 && width <= 991.98) return 'md'
        // lg - size between 992 - 1199.98
        if (width >= 992 && width <= 1199.98) return 'lg'
        // xl - size between 1200 - 1399.98
        if (width >= 1200 && width <= 1399.98) return 'xl'

        // xxl- size greater than 1399.98
        return 'xxl'
    }

    getViewportNumber = function () {
        let classInstance = this;
        switch (classInstance.getViewport()) {
            case 'xs':
                return 1;
            case 'sm':
                return 2;
            case 'md':
                return 3;
            case 'lg':
                return 4;
            case 'xl':
                return 5;
        }
        return 6;
    }


} // no ; at the end of class declaration