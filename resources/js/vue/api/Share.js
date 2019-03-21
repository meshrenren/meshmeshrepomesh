import axios from 'axios'

export default {

	createAccount(shareaccount){	
		let params = {
			shareaccount : shareaccount
		}
		return axios.post('/shareaccount/createaccount', params)
	},

	getAccounts(){	
		let params = {
			action : "shareaccount"
		}
		return axios.post('/shareaccount/get-accounts', params)
	},

	getTransaction(fk_share_id){	
		let params = {
			fk_share_id : fk_share_id
		}
		return axios.post('/shareaccount/get-transaction', params)
	},

	saveTransaction(params){	
		return axios.post('/shareaccount/save-transaction', params)
	},
}