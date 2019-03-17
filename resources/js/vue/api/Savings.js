import axios from 'axios'

export default {
	getFormPDF(account_no, type){
		if(type == 'pdf'){
			return axios({
				method: 'post',
				url: '/savings/print-withdraw',
				data: {
					account_no : account_no
				},
				responseType: 'blob'
			})
		}
		else if(type == 'print'){
			return axios({
				method: 'post',
				url: '/savings/print-withdraw',
				data: {
					account_no : account_no,
					type : type
				},
			})
		}

		
	},

	saveTransaction(params){	
		return axios.post('/savings/save-transaction', params)
	},

	getTransaction(fk_savings_id){	
		let params = {
			fk_savings_id : fk_savings_id
		}
		return axios.post('/savings/get-transaction', params)
	},
}