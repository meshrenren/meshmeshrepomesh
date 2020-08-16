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

	getLoanHistory(member_id, loan_id){
		let params = {
			member_id : member_id,
			loan_id : loan_id
		}	
		return axios.post($baseUrl + '/loan/get-loan-history', params)
	},

	getLatestLoan(loan_id, member_id){
		let params = {
			member_id : member_id,
			loan_id : loan_id
		}	
		return axios.post($baseUrl + '/loan/get-latest-info', params)
	},

	updateLoanStatus(params){
		return axios.post($baseUrl + '/loan/update-loan-status', params)
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
		}
		else if(type == 'print'){
			return axios({
				method: 'post',
				url: '/loan/print-summary',
				data: params,
			})
		}
	},
	printLedger(dataLoan, type){	
		let params = {
			dataLoan : dataLoan,
			type : type
		}
		if(type == 'pdf'){
			return axios({
				method: 'post',
				url: $baseUrl + '/loan/print-ledger',
				data: params,
				responseType: 'blob'
			})
		}
		else if(type == 'print'){
			return axios({
				method: 'post',
				url: '/loan/print-ledger',
				data: params,
			})
		}
	},

	getCurrentLoanInterestSincePreviousTransaction(accountnumber, interest_rate) {
		let params = {
			accountnumber: accountnumber,
			int_rate: interest_rate
		}

		return axios.post($baseUrl + '/loan/get-current-loan-interest', params)
	}
}