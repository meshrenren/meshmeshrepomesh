import axios from 'axios'

export default {
	getFormPDF(account_no, type){
		if(type == 'pdf'){
			return axios({
				method: 'post',
				url: '/savings/print-withdraw',
				data: {
					account_no : account_no
				},
				responseType: 'blob'
			})
		}
		else if(type == 'print'){
			return axios({
				method: 'post',
				url: '/savings/print-withdraw',
				data: {
					account_no : account_no,
					type : type
				},
			})
		}

		
	},
}