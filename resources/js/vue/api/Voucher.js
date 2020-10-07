import axios from 'axios'
let $baseUrl = window.Yii.baseUrl

export default {
	getParticulars(){
		return axios.post($baseUrl + '/general-voucher/get-particulars')
	},

	getVoucherName(){	
		let params = {
			action : 'getName'
		}
		return axios.post($baseUrl + '/general-voucher/get-name', params)
	},

	saveVoucherEntries(voucherModel, allAccounts, entryList){	
		let params = {
			voucherModel : voucherModel,
			allAccounts : allAccounts,
			entryList : entryList
		}
		return axios.post($baseUrl + '/general-voucher/save-voucher-entries', params)
	},

	getVoucher(filter){
		let params = {
			filter : filter
		}
		return axios.post($baseUrl + '/general-voucher/get-voucher', params)
	},

	getAllVouchers(){
		return axios.post($baseUrl + '/general-voucher/get-all-voucher')
	},

	getAllVoucherSummaryPerParticulars(param_sent){
		let params = {
			name : param_sent.name,
			particular_id : param_sent.particular_id,
			date_from : param_sent.date_from,
			date_to : param_sent.date_to,
		}

		return axios.post($baseUrl + '/general-voucher/get-voucher-summary-per-particulars', params)

	},

	getVoucherParticular(params){
		return axios.post($baseUrl + '/general-voucher/get-voucher-particular', params)
	},
}