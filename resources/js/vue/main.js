import Vue from 'vue'
import axios from 'axios'
import './components.js'
import API from './api/index.js'
import Element from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'
import enLocale from 'element-ui/lib/locale/lang/en'
import EventDispatcher from './service/EventDispatcher'


axios.defaults.headers.common['X-CSRF-TOKEN'] = window.Yii.csrfToken;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

Vue.prototype.$API = API
Vue.prototype.$EventDispatcher = new EventDispatcher()

Vue.use(Element, { size: 'small', enLocale });

const app = new Vue({
    el: '#vue-app'
});