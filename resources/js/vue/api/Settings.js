import axios from 'axios'
let $baseUrl = window.Yii.baseUrl

export default {
	getActiveProducts(){	
		return axios.post($baseUrl + '/settings/products')
	},
	saveLoanProduct(data){
		let params = {
			data : data
		}
		return axios.post($baseUrl + '/settings/save-loan-product', params)
	},
}