
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
require('./adminlte');
require('./jquery.slimscroll');
import VueSocketio from 'vue-socket.io';

window.Vue = require('vue');

Vue.use(VueSocketio,'http://talk.app:3000');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('example', require('./components/Example.vue'));
Vue.component('chat', require('./components/Chat.vue'));
Vue.component('members', require('./components/Members.vue'));

const app = new Vue({
    el: '#app'
});
