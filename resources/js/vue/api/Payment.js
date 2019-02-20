import axios from 'axios'

export default {

	savePaymentList(paymentList, orNum, forceAdd){	
		let params = {
			paymentList : paymentList,
			orNum : orNum,
			forceAdd : forceAdd
		}
		return axios.post('/payment/save-payment-list', params)
	},

	getPayrollRecord(){
		let params = {
			action: 'getPaymentRecord'
		}
		return axios.post('/payment/get-payment-record', params)
	}
}