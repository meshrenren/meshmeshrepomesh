import Vue from 'vue'
import axios from 'axios'
import ElementUI from 'element-ui'
import './components.js'
import API from './api/index.js'
import Element from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'
import locale from 'element-ui/lib/locale/lang/en'
import EventDispatcher from './service/EventDispatcher'
import swal from 'sweetalert2'


axios.defaults.headers.common['X-CSRF-TOKEN'] = window.Yii.csrfToken;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

Vue.prototype.$API = API
Vue.prototype.$EventDispatcher = new EventDispatcher()
Vue.prototype.$baseUrl = window.Yii.baseUrl
Vue.prototype.$swal = swal

Vue.use(Element, { size: 'mini', locale });

const app = new Vue({
    el: '#vue-app'
});