export default {
	methods: {
		__confirm(title, message) {
			return this.$swal({
                  	title: title,
                  	text: message,
                  	showCancelButton: true,
                  	confirmButtonText: this.t('Proceed'),
                  	focusConfirm: false,
                  	focusCancel: false,
                  	cancelButtonText: this.t('Cancel'),
                  	reverseButtons: true,
                  	width: '400px',
                  	padding: 0
            	})
		}
	}
}