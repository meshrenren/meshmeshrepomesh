import axios from 'axios'
let $baseUrl = window.Yii.baseUrl

export default {

	payrollExport(params){	
		
		return axios({
			method: 'post',
			url: $baseUrl + '/payment/payroll-export',
			data: params,
			responseType: 'blob'
		})
	},

	exportReportToExcel(list, title, headers) {
		return axios({
			method: 'post',
			url: $baseUrl+ '/report/default-excel-export',
			data: {
				data: list,
				title: title,
				headers: headers
			},
			responseType: 'blob'
		})
	},

	getLoanAging(params){	

		return axios.post($baseUrl + '/report/get-loan-aging', params)
	},

	getLoanArrears(params){	

		return axios.post($baseUrl + '/report/get-loan-arrears', params)
	},

	printLoanAging(data, type){	
		let params = {
			data : data,
			type : type,
		}

		if(type == 'pdf'){
			return axios({
				method: 'post',
				url: $baseUrl + '/report/print-loan-aging',
				data: params,
				responseType: 'blob'
			})
		}
		else if(type == 'print'){
			return axios({
				method: 'post',
				url: $baseUrl + '/report/print-loan-aging',
				data: params,
			})
		}
	},
}