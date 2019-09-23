import axios from 'axios'
let $baseUrl = window.Yii.baseUrl

export default {

	savingsCalculation(account_id){	
		let params = {
			account_id : account_id
		}
		return axios.post($baseUrl + '/time-deposit/savings-calculation', params)
	},

	processAccount(params){	
		return axios.post($baseUrl + '/time-deposit/process-account', params)
	},
}