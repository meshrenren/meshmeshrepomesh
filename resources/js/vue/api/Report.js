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

}