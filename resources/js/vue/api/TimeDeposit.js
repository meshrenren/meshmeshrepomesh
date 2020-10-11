import axios from 'axios'
let $baseUrl = window.Yii.baseUrl

export default {

	savingsCalculation(account_id, date = null){	
		let params = {
			account_id 	: account_id,
			date 		: date
		}
		return axios.post($baseUrl + '/time-deposit/savings-calculation', params)
	},

	processAccount(params){	
		return axios.post($baseUrl + '/time-deposit/process-account', params)
	},

	printList(data, type){
		let params = {
			data : data,
			type : type
		}

		return axios.post($baseUrl + '/time-deposit/print-list', params)
	}
}