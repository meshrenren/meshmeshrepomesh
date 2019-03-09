import axios from 'axios'

export default {
	getParticulars(){
		return axios.post('/general-voucher/get-particulars')
	},

	getVoucherName(){	
		let params = {
			action : 'getName'
		}
		return axios.post('/general-voucher/get-name', params)
	},

	saveVoucherEntries(voucherModel, entryList){	
		let params = {
			voucherModel : voucherModel,
			entryList : entryList
		}
		return axios.post('/general-voucher/save-voucher-entries', params)
	},

	getVoucher(filter){
		let params = {
			filter : filter
		}
		return axios.post('/general-voucher/get-voucher', params)
	},

	getAllVouchers(){
		return axios.post('/general-voucher/get-all-voucher')
	}
}