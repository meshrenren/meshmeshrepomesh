<template>
	<div class="savings-deposit-form">
		<div class="box box-info">
            <div class="box-header with-border">
              	<h3 class="box-title">Saving Account Transaction</h3>
            </div>
            <div class = "box-body">
	        	<el-form label-position="right" label-width="180px" :model="accountDetails">
	        	<el-row :gutter="20">
				  	<el-col :span="12">
						<el-form-item label="Member" prop="memberName">
						    <el-input v-model="accountDetails.member.fullname" :disabled = "true"></el-input>
						</el-form-item>
						<el-form-item label="ID" prop="memberID">
						    <el-input v-model="accountDetails.account_no" :disabled = "true"></el-input>
						</el-form-item>
				  	</el-col>
				  	<el-col :span="12">
						<el-form-item label="Saving Product" prop="memberName">
						    <el-input v-model="accountDetails.product.description" :disabled = "true"></el-input>
						</el-form-item>
						<span class = "pull-right"><el-button type = "info"  @click="showSearchModal = true">Search Savings Account</el-button></span>
				  	</el-col>
				</el-row>	
				</el-form>			
            	<el-form :model="savingTransactionForm" :rules="ruleTransaction" ref="savingTransactionForm" label-width="180px" >
				<h2>Savings Details</h2>
	        	<el-row :gutter="20">					
				  	<el-col :span="12">	  		
						<el-form-item label="Current Balance" prop="current_balance">
							<el-input-number v-model="savingTransactionForm.current_balance" controls-position="right" :disabled = "true"></el-input-number>
						</el-form-item>
						<el-form-item label="Deposit Amount" prop="amount">
							<el-input-number v-model="savingTransactionForm.amount" controls-position="right" :min="1"></el-input-number>
						</el-form-item>		
				  	</el-col>
				  	<el-col :span="12">	
						<el-form-item label="" prop="amount_type">
						    <el-radio-group v-model="savingTransactionForm.amount_type">
						      	<el-radio label="Cash"></el-radio>
						      	<el-radio label="Cheque"></el-radio>
						    </el-radio-group>
						</el-form-item>
						<el-form-item label="Remarks" prop="remarks">
							<el-input type = "textarea" v-model="savingTransactionForm.remarks">
							</el-input>
						</el-form-item>	
				  	</el-col>
				  	<el-col :span = "24">
				  		<span class = "pull-right"><el-button type = "primary" @click = "saveTransaction" :disabled = "accountDetails.account_no == null">Save Deposit</el-button></span>
				  	</el-col> 
	        	</el-row>
				</el-form>
				<h3>Transaction List</h3>
				<el-table :data="accountTransactionList" style="width: 50%" stripe border>
		            <el-table-column label="Amount">
		                <template slot-scope="scope">
		                    <span style="margin-left: 10px">{{ scope.row.amount }}</span>
		                </template>
		            </el-table-column>
		            <el-table-column label="Transaction Type">
		                <template slot-scope="scope">
		                    <span style="margin-left: 10px">{{ scope.row.transaction_type }}</span>
		                </template>
		            </el-table-column>
		            <el-table-column label="Type">
		                <template slot-scope="scope">
		                    <span style="margin-left: 10px">{{ scope.row.amount_type }}</span>
		                </template>
		            </el-table-column>
		            <el-table-column label="Running_balance">
		                <template slot-scope="scope">
		                    <span style="margin-left: 10px">{{ scope.row.running_balance }}</span>
		                </template>
		            </el-table-column>
		        </el-table>
            </div>
        	<search-savings-account :base-url="baseUrl" v-if = "showSearchModal" @select="populateField" @close = "showSearchModal = false" >
	  		</search-savings-account>
        </div>
    </div>
</template>
<style lang="scss">
  	@import '../../assets/site.scss';
  	@import '~noty/src/noty.scss';
</style>
<script>
	window.noty = require('noty');
    import axios from 'axios'
    import Noty from 'noty'
    import swal from 'sweetalert2/dist/sweetalert2.all.min.js'
    import SearchSavingsAccount from '../General/SearchSavingsAccount.vue' 

export default {
	props: ['dataTransaction', 'baseUrl'],
	data: function () {
    	let transaction  = {}
  		this.dataTransaction.forEach(function(detail){
  			transaction[detail] = null
  		})
		return{
			accountDetails			: {product : {description : null}, 'member' : {fullname : null}},
			savingTransactionForm 	: transaction,
			ruleTransaction 		: {},
			showSearchModal			: false,
			accountTransactionList	: null
		}
	},
	created(){
		this.ruleTransaction = {
  			amount : [{ required: true, message: 'Amount cannot be blank.', trigger: 'change' },],
  			amount_type : [{ required: true, message: 'Amount Type cannot be blank.', trigger: 'change' },],
		}
	},
    components: {
      	SearchSavingsAccount
    },
    methods:{
    	populateField(data){
    		console.log(data)
    		this.accountDetails = data
    		this.savingTransactionForm.fk_savings_id = data.account_no
    		this.savingTransactionForm.current_balance = data.balance
    		this.getTransaction()
    	},
    	cancelForm(){
    		let vm = this
    		this.accountDetails = {product : {description : null}, 'member' : {fullname : null}}
    		this.dataTransaction.forEach(function(detail){
	  			vm.savingTransactionForm[detail] = null
	  		})
    	},
    	getTransaction(){
            let data = new FormData()
            data.set('account_no', this.accountDetails.account_no)

    		axios.post(this.baseUrl+'/savings/get-transaction', data).then((result) => {
			    let res = result.data
                let type = ""
                let message = ""
                console.log(res)
                if(res.length > 0 ){
                    this.accountTransactionList = res
                    console.log("success")
                }
                else{
                    console.log("no result")
                } 
			}).catch(function (error) {
            
                console.log(error);

                if(error.response.status == 403)
                    location.reload()
            })
    	},
    	saveTransaction(){    	
    		let vm = this	
    		this.$refs.savingTransactionForm.validate((valid) => {
	          	if (valid) {
	          		swal({
	                  title: 'Save Savings Transaction?',
	                  text: "Are you sure you want to save this transaction? This action cannot be undone.",
	                  imageUrl: vm.baseUrl+'/images/attachment_icon_white.png',
	                  showCancelButton: true,
	                  confirmButtonText: 'Proceed',
	                  confirmButtonColor: '#4087C5',
	                  focusConfirm: false,
	                  focusCancel: false,
	                  cancelButtonText: 'Cancel',
	                  reverseButtons: true,
	                  background: '#ff3366',
	                  width: '400px',
	                  padding: 0
		            }).then(function() {

	            		let data = new FormData()
	            		vm.savingTransactionForm.transaction_type = "CASHDEP"

		    			data.set('accountTransaction', JSON.stringify(vm.savingTransactionForm))

		                axios.post(vm.baseUrl+'/savings/save-transaction', data).then((result) => {
			                let res = result.data
			                let type = ""
			                let message = ""
			                console.log(res)
			                if(res.length > 0 ){
			                    console.log("success")
			                }
			                else{
			                    console.log("no result")
			                } 
			                  
			            }).catch(function (error) {
			            
			                console.log(error);

			                if(error.response.status == 403)
			                    location.reload()
			            })
		            }, function(dismiss) {

		            }) 
	          	}
	          	else {
	            	return false;
	          	}
	        })
    	}
    }
}
</script>
<style lang="scss">
	.savings-deposit-form{

		.el-select{
			width: 100%;
		}

		.el-input-number{
			width: 100%;

			input{
				text-align: left;
			}
			
		}
	}
	
</style>

