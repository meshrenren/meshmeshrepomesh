import axios from 'axios'

export default {

	savePaymentList(paymentModel, allAccounts){	
		let params = {
			paymentModel : paymentModel,
			allAccounts : allAccounts
		}
		return axios.post('/payment/save-payment-list', params)
	},

	getPayrollRecord(){
		let params = {
			action: 'getPaymentRecord'
		}
		return axios.post('/payment/get-payment-record', params)
	},

	getMemberAccount(member_id){
		let params = {
			member_id: member_id
		}
		return axios.post('/payment/get-member-accounts', params)
	},

	getPaymentDetails(or_num){

		let params = {
			or_num: or_num
		}
		return axios.post('/payment/get-payment-details', params)
	}
}