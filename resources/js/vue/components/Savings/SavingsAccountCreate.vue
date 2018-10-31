<template>
	<div class="savings-account-create">
		<div class="box box-info">
	        <div class="box-header with-border">
	            <h3 class="box-title">Add Savings Account</h3>
	        </div>
	        <div class = "box-body">
	        	<el-form label-position="right" label-width="180px" :model="memberDetails">
	        		<el-row :gutter = "20">
	        			<el-col :span="18">
						  	<el-form-item label="Member ID">
						    	<el-input v-model="memberDetails.id" :disabled = "true"></el-input>
						  	</el-form-item>
						</el-col>
	        			<el-col :span="18">
						  	<el-form-item label="Member Name">
						    	<el-input v-model="memberDetails.fullname" :disabled = "true"></el-input>
						  	</el-form-item>
						</el-col>
	        			<el-col :span="6">
						  	<el-button type = "info" @click="showSearchModal = true">Search Member</el-button>
						</el-col>
					</el-row>
				</el-form>
				<el-form label-position="right" label-width="180px" :rules = "ruleAccount" ref = "savingAccountDetails" :model="savingAccountDetails">
					<el-row :gutter = "20">
						<el-col :span="18">
							<el-form-item label="Savings Product" prop = "saving_product_id">
								<el-select v-model="savingAccountDetails.saving_product_id" placeholder="Select product">
								    <el-option
								      	v-for="item in dataSavingsProduct"
								      	:key="item.value"
								      	:label="item.label"
								      	:value="item.value">
								    </el-option>
								</el-select>
						  	</el-form-item>
						</el-col>
						<el-col :span="24">
							<span class = "pull-right">
								<el-button @click="cancelForm">Cancel</el-button>
								<el-button type = "primary" @click="createAccount" :disabled = "memberDetails.id == null">Save New Account</el-button>
							</span>
						</el-col>
				  	</el-row>
				</el-form>
	        </div>
	        <search-member :base-url="baseUrl" v-if = "showSearchModal" @select="populateField" @close = "showSearchModal = false" >
	  		</search-member>
	 	</div>
	</div>
</template>
<script>
	window.noty = require('noty');
    import axios from 'axios'
    import Noty from 'noty'
    import SearchMember from '../General/SearchMember.vue'    
    import cloneDeep from 'lodash/cloneDeep'

export default {
    props: ['baseUrl', 'dataSavingsProduct', 'dataSavingsAccount'],
    data: function () {    	
    	let account  = {}
  		this.dataSavingsAccount.forEach(function(detail){
  			account[detail] = null
  		})
      	return {
      		memberDetails			: {id : null, fullname : null},
      		showSearchModal			: false,
      		savingAccountDetails 	: account,
      		ruleAccount				: null,
      	}
  	},
  	created(){
  		this.showSearchModal = true
  		this.ruleAccount = {
  			saving_product_id : [{ required: true, message: 'Product cannot be blank.', trigger: 'change' },]
  		}
  	},
    components: {
      	SearchMember
    },
    methods: {
    	populateField(data){
    		this.memberDetails = data
    		this.savingAccountDetails.member_id = data.id
    	},
    	cancelForm(){
    		let vm = this
    		this.memberDetails = {id : null, fullname : null},
    		this.dataSavingsAccount.forEach(function(detail){
	  			vm.savingAccountDetails[detail] = null
	  		})
    	},
    	createAccount(){	
    		let vm = this	
    		this.$refs.savingAccountDetails.validate((valid) => {
	          	if (valid) {
	          		vm.$swal({
		              	title: 'Create Savings Account?',
		              	text: "Are you sure you want to save this account? This action cannot be undone.",
		              	type: 'warning',
		              	showCancelButton: true,
		              	cancelButtonColor: '#d33',
		              	confirmButtonText: 'Proceed',
		              	focusConfirm: false,
		              	focusCancel: true,
		              	cancelButtonText: 'Cancel',
		              	reverseButtons: true,
		              	width: '400px',
		            }).then(function(result) {
		            	if (result.value) {

			            	let data = new FormData()

				    		data.set('account', JSON.stringify(vm.savingAccountDetails))

				            axios.post(vm.baseUrl+'/savings/create-account', data).then((result) => {
				            	let res = result.data
				            	let type = ""
				            	let message = ""
				            	if(res.success == true){
				            		type = "success"
				            		message = "New savings account successfully created."
				            		location.reload()
				            	}
				            	else{
				            		if(res.status == 'has-account'){
				            			let getProduct = vm.dataSavingsProduct.findIndex(prod => prod.value == vm.savingAccountDetails.saving_product_id)
				            			console.log(getProduct)
					            		message = vm.memberDetails.fullname + " already has savings account with "+ vm.dataSavingsProduct[getProduct].label + " product."

				            		}
				            		else{
					            		message = "Savings account not successfully created. Please try again or contact administrator."
					            		location.reload()
				            		}
				            		type = "error"
				            	}

				            	new Noty({
						            theme: 'relax',
						            type: type,
						            layout: 'topRight',
						            text: message,
						            timeout: 2500
						        }).show()

						        //vm.cancelForm()
						        //location.reload()
				            }).catch(error =>{

				            	console.log(error)
				          		new Noty({
						            theme: 'relax',
						            type: "error",
						            layout: 'topRight',
						            text: 'An error occured. Please try again or contact administrator',
						            timeout: 3000
						        }).show()

						        if(error.response && error.response.status == 403)
				    				location.reload()
				            })
				        }
			        })
	          	} else {
	            	return false;
	          	}
	        });
    	}
    }
}
</script>
<style lang="scss">
	@import '../../assets/site.scss';
	@import '~noty/src/noty.scss';

	.savings-account-create{

		.el-select{
			width: 100%;
		}
	}
	
</style>