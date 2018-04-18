import Vue from 'vue'
import axios from 'axios'
import ElementUI from 'element-ui'
import './components.js'


axios.defaults.headers.common['X-CSRF-TOKEN'] = window.Yii.csrfToken;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';



const app = new Vue({
    el: '#vue-app'
});