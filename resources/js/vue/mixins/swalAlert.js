export default {
	methods: {
		getSwalAlert(type, title, text) {
			this.$swal({
                title: title,
                text: text,
                type: type,
                showCancelButton: false,
                cancelButtonColor: '#d33',
                confirmButtonText: 'Okay',
                focusConfirm: true,
                reverseButtons: true,
                width: '400px',
            }).then(function(result) {
                console.log(result)
            })
		},
	}
}