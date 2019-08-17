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

	saveVoucherEntries(voucherModel, entryList){	
		let params = {
			voucherModel : voucherModel,
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
	}
}