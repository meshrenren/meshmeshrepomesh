<template>
	<div class="savings-deposit-form">
		<div class="box box-info">
            <div class="box-header with-border">
              	<h3 class="box-title">Deposit Savings Account</h3>
            </div>
            <div class = "box-body">
            	<el-row :gutter="20">

            		<el-col :span="12">
            			<div class = "box-content">
	            			<el-form label-position="right" label-width="180px" :model="accountDetails">
	            				<el-form-item label="Member" prop="memberName">
								    <el-input v-model="accountDetails.member.fullname" :disabled = "true"></el-input>
								</el-form-item>
								<el-form-item label="ID" prop="memberID">
								    <el-input v-model="accountDetails.account_no" :disabled = "true"></el-input>
								</el-form-item>
								<el-form-item label="Saving Product" prop="memberName">
								    <el-input v-model="accountDetails.product.description" :disabled = "true"></el-input>
								</el-form-item>
								<el-button type = "info"  @click="showSearchModal = true">Search Savings Account</el-button>

	            			</el-form>
							<hr>
							<div class = "transaction-list">
		            			<h4>Transaction List</h4>
								<el-table :data="accountTransactionList" style="width: 100%" stripe border>
						            <el-table-column label="Transaction ID">
						                <template slot-scope="scope">
						                    <span style="margin-left: 10px">{{ scope.row.id }}</span>
						                </template>
						            </el-table-column>
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
						            <el-table-column label="Running Balance">
						                <template slot-scope="scope">
						                    <span style="margin-left: 10px">{{ scope.row.running_balance }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Remarks">
						                <template slot-scope="scope">
						                    <span style="margin-left: 10px">{{ scope.row.remarks }}</span>
						                </template>
						            </el-table-column>
						        </el-table>
						    </div>
	            		</div>
            		</el-col>

            		<el-col :span="12">
            			<div class = "box-content">
            				<el-form :model="savingTransactionForm" :rules="ruleTransaction" ref="savingTransactionForm" label-width="180px" >
            					<el-form-item label="Current Balance" prop="current_balance">
									<el-input-number v-model="savingTransactionForm.current_balance" controls-position="right" :disabled = "true"></el-input-number>
								</el-form-item>
								<el-form-item label="Deposit Amount" prop="amount">
									<el-input-number v-model="savingTransactionForm.amount" controls-position="right" :min="1"></el-input-number>
								</el-form-item>	
								<el-form-item label="" prop="transaction_type">
								    <el-radio-group v-model="savingTransactionForm.transaction_type">
								      	<el-radio label="Cash"></el-radio>
								      	<el-radio label="Cheque"></el-radio>
								    </el-radio-group>
								</el-form-item>
								<el-form-item label="Remarks" prop="remarks">
									<el-input type = "textarea" v-model="savingTransactionForm.remarks" :rows = "5">
									</el-input>
								</el-form-item>	
								<el-button type = "primary" @click = "saveTransaction" :disabled = "accountDetails.account_no == null">Save Deposit</el-button>
								<el-button type = "primary" @click = "printTransaction" :disabled = "accountDetails.account_no == null">Print Deposit</el-button>
            				</el-form>
            			</div>
            		</el-col>

            	</el-row>
	        	
            </div>
        	<search-savings-account :base-url="baseUrl" :show-modal = "showSearchModal" @select="populateField" @close = "showSearchModal = false" >
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
    import SearchSavingsAccount from '../General/SearchSavingsAccount.vue' 

export default {
	props: ['dataTransaction', 'baseUrl'],
	data: function () {
    	let transaction  = {}
  		this.dataTransaction.forEach(function(detail){
  			transaction[detail] = null
  		})
  		transaction['transaction_type'] = "Cash"
		return{
			accountDetails			: {product : {description : null}, 'member' : {fullname : null}},
			savingTransactionForm 	: transaction,
			ruleTransaction 		: {},
			showSearchModal			: false,
			accountTransactionList	: null
		}
	},
	created(){
		this.showSearchModal = true
		this.ruleTransaction = {
  			amount : [{ required: true, message: 'Amount cannot be blank.', trigger: 'change' },],
  			transaction_type : [{ required: true, message: 'Transaction type cannot be blank.', trigger: 'change' },],
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
	          		vm.$swal({
	                  title: 'Save Savings Transaction?',
	                  text: "Are you sure you want to save this transaction? This action cannot be undone.",
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
		            		let data = new FormData()
		            		if(vm.savingTransactionForm.transaction_type == "Cash")
		            			vm.savingTransactionForm.transaction_type = "CASHDEP"
		            		if(vm.savingTransactionForm.transaction_type == "Cheque")
		            			vm.savingTransactionForm.transaction_type = "CHEQUEDEP"

			    			data.set('accountTransaction', JSON.stringify(vm.savingTransactionForm))

			                axios.post(vm.$baseUrl+'/savings/save-transaction', data).then((result) => {
				                let res = result.data
				                let type = ""
				                let message = ""
				                console.log(res)
				                if(res.success > 0 ){
				                	id = res.data
				                    console.log("success")
				                    vm.$swal({
					                  title: 'Print Form?',
					                  type: 'warning',
					                  showCancelButton: true,
					                  cancelButtonColor: '#d33',
					                  confirmButtonText: 'Print',
					                  focusConfirm: false,
					                  focusCancel: true,
					                  cancelButtonText: 'Cancel',
					                  reverseButtons: true,
						            }).then(function(result) {
						            	if (result.value) {
						            		window.open(vm.$baseUrl+'/savings/pdf-print?tid=', '_blank');
						            	}
						            })
				                }
				                else{
				                    console.log("no result")
				                } 
				                location.reload()
				                  
				            }).catch(function (error) {
				            
				                console.log(error);

				                if(error.response.status == 403)
				                    location.reload()
				            })
				        }
				        else{

				        }
		            }) 
	          	}
	          	else {
	            	return false;
	          	}
	        })
    	},
    	printTransaction(){
    		axios.post(this.$baseUrl+'/savings/pdf', null).then((result) => {
                console.log(result)
            }).catch(function (error) {
            
                console.log(error);

                if(error.response.status == 403)
                    location.reload()
            })

    	}
    }
}
</script>
<style lang="scss">
	@import '../../assets/site.scss';
	@import '~noty/src/noty.scss';
	
	.savings-deposit-form{

		.box{
	  		.el-form-item{
			    margin-bottom: 5px !important;
			}
  		}

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

