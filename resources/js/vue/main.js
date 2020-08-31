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
import CalculationHelper from './service/CalculationHelper'
import AccountHelper from './service/AccountHelper'
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
Vue.prototype.$ch = new CalculationHelper()
Vue.prototype.$ah = new AccountHelper()
Vue.prototype.$baseUrl = window.Yii.baseUrl
Vue.prototype.$systemDate = window.coopData.systemDate
Vue.prototype.$cutOffDate = window.coopData.cutOffDate

Vue.use(Element, { size: 'mini', locale });
Vue.use(VueSweetalert2);

import './assets/main.scss'
import 'noty/src/noty.scss'
const app = new Vue({
    el: '#vue-app'
});

axios.interceptors.response.use( (response) => {
    return response;
}, (error) => {
    if(error.response){
        if (error.response.status == 401) {
          	app.$alert('Opss! Your session may have been expired. Please login.', 'UNAUTHENTICATED', {
            	confirmButtonText: 'OK',
            	callback: action => {
                	location.reload()
            	}
          	});
        } else if (error.response.status == 403) {
            app.$alert('Opss! You do not have enough permission or your session may have expired. Refresh the page to know if you need to login again.', 'FORBIDDEN', {
                confirmButtonText: 'OK',
                callback: action => {
                    location.reload()
                }
            });
        } else if (error.response.status >= 500) {
            app.$alert('Opss! Something went wrong. Please report this to your technical support.', 'SERVER ERROR', {
                confirmButtonText: 'OK',
                callback: action => {
                    // location.reload()
                }
            });
        }
    }
    return Promise.reject(error);
});
