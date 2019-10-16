import axios from 'axios'
let $baseUrl = window.Yii.baseUrl

export default {
	createAccount(params){	
		return axios.post('/savings/create-account', params)
	},

	getFormPDF(account_no, amount, type){
		if(type == 'pdf'){
			return axios({
				method: 'post',
				url: $baseUrl + '/savings/print-withdraw',
				data: {
					account_no : account_no,
					amount : amount,
					type : type
				},
				responseType: 'blob'
			})
		}
		else if(type == 'print'){
			return axios({
				method: 'post',
				url: $baseUrl + '/savings/print-withdraw',
				data: {
					account_no : account_no,
					amount : amount,
					type : type
				},
			})
		}

		
	},

	saveTransaction(params){	
		return axios.post($baseUrl + '/savings/save-transaction', params)
	},


	getAccounts(){	
		let params = {
			action : "savingsaccount"
		}
		return axios.post($baseUrl + '/savings/get-account', params)
	},

	getTransaction(fk_savings_id){	
		let params = {
			fk_savings_id : fk_savings_id
		}
		return axios.post($baseUrl + '/savings/get-transaction', params)
	},
}