import axios from 'axios'

export default {
	printList(data, type, accountType){	
		let params = {
			data : data,
			type : type,
			accountType : accountType
		}
		if(type == 'pdf'){
			return axios({
				method: 'post',
				url: '/site/print-list',
				data: params,
				responseType: 'blob'
			})
		}
		else if(type == 'print'){
			return axios({
				method: 'post',
				url: '/site/print-list',
				data: params,
			})
		}
	},
}