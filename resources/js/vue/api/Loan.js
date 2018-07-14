import axios from 'axios'

export default {
	getAccounLoanInfo(member_id){
		let params = {
			member_id : member_id
		}	
		return axios.post('/loan/get-account-loan-info', params)
	},

	evaluateLoan(params){
		return axios.post('/loan/evaluate-loan', params)
	}
}