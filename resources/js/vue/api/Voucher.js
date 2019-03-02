import axios from 'axios'

export default {

	getVoucherName(){	
		let params = {
			action : 'getName'
		}
		return axios.post('/general-voucher/get-name', params)
	},

	saveVoucherEntries(voucherList, gvNumber, forceAdd){	
		let params = {
			voucherList : voucherList,
			gvNumber : gvNumber,
			forceAdd : forceAdd
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