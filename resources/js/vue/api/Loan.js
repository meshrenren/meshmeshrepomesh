import axios from 'axios'
let $baseUrl = window.Yii.baseUrl

export default {
	getAccounLoanInfo(member_id){
		let params = {
			member_id : member_id
		}	
		return axios.post($baseUrl + '/loan/get-account-loan-info', params)
	},

	getLoanTransaction(loan_account){
		let params = {
			loan_account : loan_account
		}	
		return axios.post($baseUrl + '/loan/get-transaction', params)
	},

	getLatestLoan(loan_id, member_id){
		let params = {
			member_id : member_id,
			loan_id : loan_id
		}	
		return axios.post($baseUrl + '/loan/get-latest-info', params)
	},

	evaluateLoan(params){
		return axios.post($baseUrl + '/loan/evaluate-loan', params)
	},

	applyLoan(params)
	{
		return axios.post($baseUrl + 'loan/verify-loan', params)
	},

	approveLoan(params)
	{
		return axios.post($baseUrl + '/loan/apply-loan', params)
	},
	releaseLoanVoucher(voucherModel, entryList){
		let params = {
			voucherModel : voucherModel,
			entryList : entryList
		}	
		return axios.post($baseUrl + '/loan/save-release-loan', params)
	},
	printSummary(dataLoan, type){	
		let params = {
			dataLoan : dataLoan,
			type : type
		}
		if(type == 'pdf'){
			return axios({
				method: 'post',
				url: $baseUrl + '/loan/print-summary',
				data: params,
				responseType: 'blob'
			})
		}/*
		else if(type == 'print'){
			return axios({
				method: 'post',
				url: '/site/print-list',
				data: params,
			})
		}*/
	},
}