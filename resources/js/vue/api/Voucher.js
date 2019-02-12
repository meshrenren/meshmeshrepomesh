import axios from 'axios'

export default {

	getVoucherName(){	
		let params = {
			action : 'getName'
		}
		return axios.post('/general-voucher/get-name', params)
	},

	saveVoucherEntries(voucherList){	
		let params = {
			voucherList : 'voucherList'
		}
		return axios.post('/general-voucher/save-voucher-entries', params)
	},

	getVoucher(filter){
		let params = {
			filter : 'filter'
		}
		return axios.post('/general-voucher/get-voucher', params)
	}
}