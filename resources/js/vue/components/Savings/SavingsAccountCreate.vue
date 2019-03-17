<template>
	<div class="savings-account-create">
		<div class="box box-info">
	        <div class="box-header with-border">
	            <h3 class="box-title">Savings Account</h3>
	        </div>
	        <div class = "box-body">
	        	<el-row :gutter = "20">			
					<el-col :span="12">
						<el-input v-model="accountFilter" autofocus placeholder = "Search Account">
		                    <!-- <el-button slot="append" type = "primary" @click="getMember()">Find Member</el-button> -->
		                </el-input>
						<el-table :data="accountListData"  height="450" stripe border style = "margin-top:10px;">
				            <el-table-column label="Account Number">
				                <template slot-scope="scope">
				                    <span style="margin-left: 10px">{{ scope.row.account_no }}</span>
				                </template>
				            </el-table-column>
                            <el-table-column label="Account Name">
                                <template slot-scope="scope">
                                    <span>{{ scope.row.account_name }}</span>
                                </template>
                            </el-table-column>
				            <el-table-column label="Balance">
				                <template slot-scope="scope">
				                    <span style="margin-left: 10px">{{ $nf.numberFixed(scope.row.balance, 2) }}</span>
				                </template>
				            </el-table-column>
				        </el-table>
					</el-col>		
					<el-col :span="12">
						<h3>Add New Account</h3>
						<el-form label-position="right" label-width="180px" :rules = "ruleAccount" ref = "savingAccountDetails" :model="savingAccountDetails">
							<el-form-item label="Type" prop = "type">
								<el-select v-model="savingAccountDetails.type" placeholder="Select type" @change = "selectType">
								    <el-option abel="Member" value="Member"></el-option>
								    <el-option abel="Group" value="Group"></el-option>
								</el-select>
						  	</el-form-item>
						  	<el-form-item label="Name" prop = "account_name">
				                <el-input v-model="savingAccountDetails.account_name" :disabled = "savingAccountDetails.type == 'Member' ? true:false">
				                    <el-button slot="append" type = "primary" @click="getMember()" v-if = "savingAccountDetails.type == 'Member'">Find Member</el-button>
				                </el-input>
				            </el-form-item>
							<el-form-item label="Savings Product" prop = "saving_product_id">
								<el-select v-model="savingAccountDetails.saving_product_id" placeholder="Select product">
								    <el-option
								      	v-for="item in dataSavingsProduct"
								      	:key="item.value"
								      	:label="item.label"
								      	:value="String(item.value)">
								    </el-option>
								</el-select>
						  	</el-form-item>
						  	<div class = "signatory" v-if = "savingAccountDetails.type == 'Group'">
								<el-button type = "info" @click="getSignatory()">Add Signatory</el-button>
								<el-table :data="signatoryList" style="width: auto; margin-top:10px;" stripe border>
						            <el-table-column label="Member Signatory">
						                <template slot-scope="scope">
						                    <span style="margin-left: 10px">{{ scope.row.fullname }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Action">
						                <template slot-scope="scope">
						                    <el-button type = "danger" @click="removeSignatory(scope.row)">Remove</el-button>
						                </template>
						            </el-table-column>
						        </el-table>
							</div>
						</el-form>
						<div style="margin-top: 10px;">
							<span class = "pull-right">
								<el-button @click="cancelForm">Cancel</el-button>
								<el-button type = "primary" @click="createAccount">Save New Account</el-button>
							</span>
						</div>
					</el-col>
				</el-row>
	        </div>
	        <!-- <search-member :base-url="baseUrl" v-if = "showSearchModal" @select="populateField" @close = "showSearchModal = false" >
	        	  		</search-member> -->
	  		<search-member :base-url="baseUrl" :show-modal = "showSearchModal" @select="populateField" @close = "showSearchModal = false" >
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
    import _forEach from 'lodash/forEach'

export default {
    props: ['baseUrl', 'dataSavingsProduct', 'dataAccountList', 'dataSavingsAccount'],
    data: function () {    	
    	let account  = cloneDeep(this.dataSavingsAccount)
      	return {
      		memberDetails			: {id : null, fullname : null},
      		showSearchModal			: false,
      		savingAccountDetails 	: account,
      		ruleAccount				: null,
      		isGet 					: "",
      		signatoryList			: [],
      		accountList 			: this.dataAccountList,
      		accountFilter 			: ""
      	}
  	},
  	created(){
  		//this.showSearchModal = true

  		this.savingAccountDetails.type = "Member"
  		this.savingAccountDetails.saving_product_id = "1"

  		this.ruleAccount = {
  			saving_product_id : [{ required: true, message: 'Product cannot be blank.', trigger: 'change' }],
  			account_name : [{ required: true, message: 'Name cannot be blank.', trigger: 'change' }]
  		}
  	},
    components: {
      	SearchMember
    },
    computed:{
    	accountListData(){
    		let datalist = this.accountList
            let filterKey = this.accountFilter

            _forEach(datalist, function(element, index) {
            	if(element.type == "Member" && element.member){
            		element.account_name = element.member.last_name + ', ' + element.member.first_name + ' ' + element.member.middle_name
            	}
            	
            })

            if (filterKey) {
                if(datalist){
                    datalist = datalist.filter(function (row) {
                    	return  String(row.account_name).toLowerCase().indexOf(filterKey) > -1
                    })
                }
            }

            return datalist
    	}
    },
    methods: {
    	selectType(val){
    		if(val == "Member"){
    			if(!this.savingAccountDetails.account_name){
    				this.isGet = "AccountMember"
    				this.showSearchModal = true
    			}
    		}
    		else if(val == "Group"){
    			this.savingAccountDetails.account_name = ""
    			this.savingAccountDetails.member_id = null
    		}
    	},
    	getSignatory(){
    		this.isGet = "AccountSignatory"
    		this.showSearchModal = true
    	},
    	getMember(){
    		this.isGet = "AccountMember"
    		this.showSearchModal = true
    	},
    	populateField(data){
    		if(this.isGet == "AccountMember"){
    			console.log("data", data)
    			this.savingAccountDetails.account_name = data.fullname
    			this.savingAccountDetails.member_id = data.id
    		}
    		else if(this.isGet == "AccountSignatory"){
    			let getInd = this.signatoryList.findIndex(mem => Number(mem.id) == Number(data.id))
    			if(getInd < 0){
    				this.signatoryList.push(data)
    			}
    		}
    		//this.memberDetails = data
    		
    	},
    	cancelForm(){
    		let vm = this
    		this.memberDetails = {id : null, fullname : null},
    		this.dataSavingsAccount.forEach(function(detail){
	  			vm.savingAccountDetails[detail] = null
	  		})
    	},
    	removeSignatory(data){
    		let index = this.signatoryList.findIndex(ci => {return Number(ci.id) == Number(data.id)})		
			if (index >= 0){ 
				this.signatoryList.splice(index, 1)
			}
    	},
    	createAccount(){	
    		let vm = this	
    		if(this.savingAccountDetails.type == "Group" && this.signatoryList.length == 0)
    		{
    			new Noty({
		            theme: 'relax',
		            type: "error",
		            layout: 'topRight',
		            text: 'Please add signatory member for group savings account.',
		            timeout: 2500
		        }).show()

		        return false
    		}
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

			            	let data = {
			            		account : vm.savingAccountDetails,
			            		signatoryList : vm.signatoryList
			            	}

				    		//data.set('account', JSON.stringify(vm.savingAccountDetails))

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
					            		//location.reload()
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