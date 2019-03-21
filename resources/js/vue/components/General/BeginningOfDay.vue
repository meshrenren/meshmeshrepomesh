<template>
	<div class="beginningofday" v-loading = "pageLoading">
		<button class = "btn btn-primary btn-lg" @click = "beginTheDay()" :disabled = "disabled">BEGIN THE DAY</button>
		<p class = "date">Current Date: {{ $df.formatDate(currentDate, 'MM/DD/YYYY') }}</p>
	</div>
</template>
<style lang="scss">
  	@import '../../assets/site.scss';
  	@import '~noty/src/noty.scss';
</style>
<script> 
	import axios from 'axios'

    import swalAlert from '../../mixins/swalAlert.js'
export default {
    mixins: [swalAlert],
	props: ['baseUrl', 'currentDate'],
	data: function () {
		return{
			disabled 		: false,
			pageLoading 	: false
		}	
	},
	methods:{
		beginTheDay(){
			console.log("Here")
			let vm = this
      		this.$swal({
              title: 'Begin The Day?',
              text: "Are you sure you want to begin the day? This action cannot be undone.",
              type: 'warning',
              showCancelButton: true,
              cancelButtonColor: '#d33',
              confirmButtonText: 'Proceed',
              focusConfirm: false,
              focusCancel: true,
              cancelButtonText: 'Cancel',
              reverseButtons: true,
            }).then(function(result) {
            	if (result.value) {
            		vm.pageLoading = true
            		vm.disabled = true
            		let data = {
            			action : "beginTheDay"
            		}

            		axios.post(vm.baseUrl+'/site/begin-the-day', data).then((result) => {
		                let res = result.data
		                if(res){
		                	document.location.href="/";
		                }else{
		                	vm.getSwalAlert("error", "Error on Beginning of Day", "Please contact administrator or developer.")
		                }
		            })
		            .catch(err => {
		                console.log(err)
		            })
		            .then(_ => { 
		                vm.pageLoading = false
		                //vm.disabled = true
		            })
            	}
            })
		}
	}
}
</script>
<style lang="scss">
	.beginningofday{
		text-align: center;
		padding-top: 20px;

		button{
		    font-size: 50px;
		    padding: 20px 40px;
		}

		.date{
		    font-size: 18px;
		    font-weight: bold;
		    margin-top: 20px;
		}
	}
</style>