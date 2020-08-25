/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('example-component', require('./components/ExampleComponent.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
});



export default async function ({ app }) {
    if (!app.$auth.loggedIn) {
        return
    }
    const auth = app.$auth;
    const authStrategy = auth.strategy.name;
    if(authStrategy === 'facebook' || authStrategy === 'google'){
        const token = auth.getToken(authStrategy).substr(7)
        const authStrategyConverted = authStrategy === 'facebook' ? 'fb' : 'google';
        const url = `/user/signup/${authStrategyConverted}?token=${token}`;
        try {
            const {data} = await app.$axios.$post(url, null);
            auth.setToken('local', "Bearer "+ data.access_token);
            setTimeout( async () => {
                auth.setStrategy('local');
                setTimeout( async () => {
                    await auth.fetchUser();
                })
            });
        } catch (e) {
            console.log(e);
        }
    }
}
