import axios from 'axios'

export default {
	
	getAccounts(type, id, name){
		let params = {
			type 	: type,
			id 		: id,
			name 	: name
		}
		return axios.post('/member/get-all-accounts', params)
	},

	changePassword(form, memberId){
		let params = {
			form 		: form,
			memberId 	: memberId
		}
		return axios.post('/member/change-password', params)
	}

}