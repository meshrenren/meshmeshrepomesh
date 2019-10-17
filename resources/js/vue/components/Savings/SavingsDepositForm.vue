<template>
	<div class="savings-deposit-form" v-loading = "pageLoading">
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
								<el-table :data="accountTransactionList" height = "400px" stripe border>
						            <el-table-column label="ID" width = "50px">
						                <template slot-scope="scope">
						                    <span>{{ scope.row.id }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Amount">
						                <template slot-scope="scope">
						                    <span>{{ $nf.formatNumber(scope.row.amount) }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Reference No">
						                <template slot-scope="scope">
						                    <span>{{ scope.row.ref_no }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Transaction Type">
						                <template slot-scope="scope">
						                    <span>{{ scope.row.transaction_type }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Running Balance">
						                <template slot-scope="scope">
						                    <span>{{ $nf.formatNumber(scope.row.running_balance) }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Remarks">
						                <template slot-scope="scope">
						                    <span>{{ scope.row.remarks }}</span>
						                </template>
						            </el-table-column>
						        </el-table>
						    </div>
	            		</div>
            		</el-col>

            		<el-col :span="12">
            			<div class = "box-content">
            				<el-form :model="savingTransactionForm" :rules="ruleTransaction" ref="savingTransactionForm" label-width="200px" >
            					<el-form-item label="Current Balance" prop="current_balance">
									<el-input-number v-model="savingTransactionForm.current_balance" controls-position="right" :disabled = "true"></el-input-number>
								</el-form-item>
								<el-form-item label="Deposit Amount" prop="amount">
									<el-input-number v-model="savingTransactionForm.amount" controls-position="right" :min="1"></el-input-number>
								</el-form-item>	
								<el-form-item label="" prop="type">
								    <el-radio-group v-model="savingTransactionForm.type">
								      	<el-radio label="Cash"></el-radio>
								      	<!-- <el-radio label="Cheque"></el-radio> -->
								    </el-radio-group>
								</el-form-item>
								<el-form-item label="Reference No. (OR Number)" prop="ref_no">
									<el-input type = "text" v-model="savingTransactionForm.ref_no"></el-input>
								</el-form-item>	
								<el-form-item label="Remarks" prop="remarks">
									<el-input type = "textarea" v-model="savingTransactionForm.remarks" :rows = "5">
									</el-input>
								</el-form-item>	
								<el-form-item>
									<el-button class = "pull-right" type = "primary" @click = "saveTransaction" :disabled = "accountDetails.account_no == null">Process</el-button>
								</el-form-item>
								
								<!-- <el-button type = "primary" @click = "printTransaction" :disabled = "accountDetails.account_no == null">Print Deposit</el-button> -->
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
    import cloneDeep from 'lodash/cloneDeep'  
    import SearchSavingsAccount from '../General/SearchSavingsAccount.vue' 

    import swalAlert from '../../mixins/swalAlert.js'

export default {
    mixins: [swalAlert],
	props: ['dataTransaction', 'baseUrl'],
	data: function () {
    	let transaction  = cloneDeep(this.dataTransaction)
  		transaction['type'] = "Cash"

		return{
			accountDetails			: {product : {description : null}, 'member' : {fullname : null}},
			savingTransactionForm 	: transaction,
			ruleTransaction 		: {},
			showSearchModal			: false,
			accountTransactionList	: null,
			pageLoading 			: false
		}
	},
	created(){
		this.showSearchModal = true
		this.ruleTransaction = {
  			amount : [{ required: true, message: 'Amount cannot be blank.', trigger: 'change' },],
  			transaction_type : [{ required: true, message: 'Transaction type cannot be blank.', trigger: 'change' },],
  			ref_no : [{ required: true, message: 'Reference Number cannot be blank.', trigger: 'change' },],
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
    		this.pageLoading = true

            this.$API.Savings.getTransaction(this.accountDetails.account_no)
            .then(result => {
                var res = result.data
                if(res.length > 0 ){
                    this.accountTransactionList = res
                }
            })
            .catch(err => {
                console.log(err)
            })
            .then(_ => { 
                this.pageLoading = false
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
		            		vm.pageLoading = true

		            		let accountTransaction = cloneDeep(vm.savingTransactionForm)
		            		if(accountTransaction.type == "Cash")
		            			accountTransaction.transaction_type = "CASHDEP"
		            		if(accountTransaction.type == "Cheque")
		            			accountTransaction.transaction_type = "CHEQUEDEP"

							let params = {
								accountTransaction : accountTransaction,
								product : vm.accountDetails.product
							}

			    			vm.$API.Savings.saveTransaction(params)
				            .then(result => {
				                var res = result.data
				                if(res.success){
				                    new Noty({
				                        theme: 'relax',
				                        type: 'success',
				                        layout: 'topRight',
				                        text: 'Savings Deposit successfully processed.',
				                        timeout: 3000
				                    }).show();
				                    
				                    location.reload()
				                }
				                else{

				                    let title = "Error: Not Saved"
				                    let type = 'warning'
				                    let text = res.errorMessage
				                    if(res.error == 'ERROR_HASRN'){
				                        title = 'Error: Reference Number Exist'
				                        text = "Reference Number " + accountTransaction.ref_no + " already exist."
				                        type = "error"
				                    }

				                    vm.getSwalAlert(type, title, text)
				                }
				            })
				            .catch(err => {
				                console.log(err)
				            })
				            .then(_ => { 
				                vm.pageLoading = false
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

