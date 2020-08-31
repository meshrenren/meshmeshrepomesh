import axios from 'axios'
let $baseUrl = window.Yii.baseUrl

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
				url: $baseUrl + '/site/print-list',
				data: params,
				responseType: 'blob'
			})
		}
		else if(type == 'print'){
			return axios({
				method: 'post',
				url: $baseUrl + '/site/print-list',
				data: params,
			})
		}
	},

	printBalance(data, type, accountType){	
		let params = {
			data : data,
			type : type,
			accountType : accountType
		}
		if(type == 'pdf'){
			return axios({
				method: 'post',
				url: $baseUrl + '/site/print-balance',
				data: params,
				responseType: 'blob'
			})
		}
		else if(type == 'print'){
			return axios({
				method: 'post',
				url: $baseUrl + '/site/print-balance',
				data: params,
			})
		}
	},

	getParticularsByName(names = null, category = null){	
		let params = {
			names : names,
			category : category
		}	
		return axios.post($baseUrl + '/site/get-particulars', params)
	},

	getParticularsVoucher(params){	
		return axios.post($baseUrl + '/site/get-particulars', params)
	}
}