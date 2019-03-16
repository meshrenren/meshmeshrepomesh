import Vue from 'vue'
import axios from 'axios'
import ElementUI from 'element-ui'
import './components.js'
import API from './api/index.js'
import Element from 'element-ui'
import locale from 'element-ui/lib/locale/lang/en'
import EventDispatcher from './service/EventDispatcher'
import NumberFormatter from './service/NumberFormatter'
import DateFormatter from './service/DateFormatter'
import VueSweetalert2 from 'vue-sweetalert2';
import moment from 'moment'
/* CSS */

import 'element-ui/lib/theme-chalk/index.css'

axios.defaults.headers.common['X-CSRF-TOKEN'] = window.Yii.csrfToken;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

Vue.prototype.$API = API
Vue.prototype.moment = moment
Vue.prototype.$EventDispatcher = new EventDispatcher()
Vue.prototype.$nf = new NumberFormatter()
Vue.prototype.$df = new DateFormatter()
Vue.prototype.$baseUrl = window.Yii.baseUrl

Vue.use(Element, { size: 'mini', locale });
Vue.use(VueSweetalert2);

const app = new Vue({
    el: '#vue-app'
});