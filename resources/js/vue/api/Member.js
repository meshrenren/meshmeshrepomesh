import axios from 'axios'
let $baseUrl = window.Yii.baseUrl

export default {
	
	getAccounts(type, id, name){
		let params = {
			type 	: type,
			id 		: id,
			name 	: name
		}
		return axios.post($baseUrl + '/member/get-all-accounts', params)
	},

	changePassword(form, memberId){
		let params = {
			form 		: form,
			memberId 	: memberId
		}
		return axios.post($baseUrl + '/member/change-password', params)
	}

}