import axios from 'axios'
let $baseUrl = window.Yii.baseUrl

export default {

	savePaymentList(paymentModel, allAccounts){	
		let params = {
			paymentModel : paymentModel,
			allAccounts : allAccounts
		}
		return axios.post($baseUrl + '/payment/save-payment-list', params)
	},

	getPayrollRecord(){
		let params = {
			action: 'getPaymentRecord'
		}
		return axios.post($baseUrl + '/payment/get-payment-record', params)
	},

	getMemberAccount(member_id){
		let params = {
			member_id: member_id
		}
		return axios.post($baseUrl + '/payment/get-member-accounts', params)
	},

	getPaymentDetails(or_num){

		let params = {
			or_num: or_num
		}
		return axios.post($baseUrl + '/payment/get-payment-details', params)
	},

	getPaymentForCancellation(or_num) {
		let params = {
			or_num: or_num
		}
		return axios.post($baseUrl + '/payment/get-payment-list-with-reference', params)
	},

	cancelPaymentCollection(payments) {
		let params = {
			payments: payments
		}

		return axios.post($baseUrl + '/payment/get-payment-list-with-reference', params)
	},

	setPaymentPayroll(params) {
		return axios.post($baseUrl + '/payment/set-payment-payroll', params)
	},

	getPaymentParticular(params){
		return axios.post($baseUrl + '/payment/get-payment-particular', params)
	},
}