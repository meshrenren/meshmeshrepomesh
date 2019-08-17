import axios from 'axios'
let $baseUrl = window.Yii.baseUrl

export default {

	createAccount(shareaccount){	
		let params = {
			shareaccount : shareaccount
		}
		return axios.post($baseUrl + '/shareaccount/createaccount', params)
	},

	getAccounts(){	
		let params = {
			action : "shareaccount"
		}
		return axios.post($baseUrl + '/shareaccount/get-accounts', params)
	},

	getTransaction(fk_share_id){	
		let params = {
			fk_share_id : fk_share_id
		}
		return axios.post($baseUrl + '/shareaccount/get-transaction', params)
	},

	saveTransaction(params){	
		return axios.post($baseUrl + '/shareaccount/save-transaction', params)
	},
}