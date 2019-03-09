import axios from 'axios'

export default {
	getAccounLoanInfo(member_id){
		let params = {
			member_id : member_id
		}	
		return axios.post('/loan/get-account-loan-info', params)
	},

	getLatestLoan(loan_id, member_id){
		let params = {
			member_id : member_id,
			loan_id : loan_id
		}	
		return axios.post('/loan/get-latest-info', params)
	},

	evaluateLoan(params){
		return axios.post('/loan/evaluate-loan', params)
	},

	applyLoan(params)
	{
		return axios.post('loan/apply-loan', params)

	}
}