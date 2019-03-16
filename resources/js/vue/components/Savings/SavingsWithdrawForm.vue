<template>
	<div class="savings-deposit-form" v-loading = "pageLoading">
		<div class="box box-info">
            <div class="box-header with-border">
              	<h3 class="box-title">Withdraw Savings Account</h3>
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
						            <el-table-column label="Reference No">
						                <template slot-scope="scope">
						                    <span style="margin-left: 10px">{{ scope.row.reference_number }}</span>
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
						        <el-button class = "mt-10" type = "default"  @click="printForm('print')" v-if = "accountDetails.account_no">Print Form</el-button>
						    </div>
	            		</div>
            		</el-col>

            		<el-col :span="12">
            			<div class = "box-content">
            				<el-form :model="savingTransactionForm" :rules="ruleTransaction" ref="savingTransactionForm" label-width="200px" >
            					<el-form-item label="Current Balance" prop="current_balance">
									<el-input-number v-model="savingTransactionForm.current_balance" controls-position="right" :disabled = "true"></el-input-number>
								</el-form-item>
								<el-form-item label="Withdraw Amount" prop="amount">
									<el-input-number v-model="savingTransactionForm.amount" controls-position="right" :min="1" :max = "savingTransactionForm.current_balance"></el-input-number>
								</el-form-item>
								<el-form-item label="Reference No. (GV No.)" prop="reference_number">
									<el-input type = "text" v-model="savingTransactionForm.reference_number"></el-input>
								</el-form-item>		
								<el-form-item label="Remarks" prop="remarks">
									<el-input type = "textarea" v-model="savingTransactionForm.remarks" :rows = "5">
									</el-input>
								</el-form-item>	
								<el-form-item>
									<el-button class = "pull-right" type = "primary" @click = "saveTransaction" :disabled = "accountDetails.account_no == null">Process</el-button>
								</el-form-item>
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
    import VoucherForm from '../Voucher/VoucherForm.vue' 

	import fileExport from '../../mixins/fileExport'
    import swalAlert from '../../mixins/swalAlert.js'

export default {
	mixins: [fileExport, swalAlert],
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
			dataLoad				: false,
			pageLoading 			: false
		}
	},
	created(){
		this.showSearchModal = true
		this.ruleTransaction = {
  			amount : [{ required: true, message: 'Amount cannot be blank.', trigger: 'change' },],
  			transaction_type : [{ required: true, message: 'Transaction type cannot be blank.', trigger: 'change' },],
  			reference_number : [{ required: true, message: 'Reference Number cannot be blank.', trigger: 'change' },],
		}
	},
    components: {
      	SearchSavingsAccount,
      	VoucherForm
    },
    methods:{
    	printForm(type){			
			this.$API.Savings.getFormPDF(this.accountDetails.account_no, type)
			.then(result => {
				let res = result.data
				if(type == 'pdf'){
					this.exporter(type, 'Savings Withdraw', res)
				}
				else if(type == 'print'){
					this.winPrint(res.data, 'Savings Withdraw')
				}
			})
			.catch(err => { console.log(err)})
			.then(_ => { this.dataLoad = false })

    		//window.location.href = this.$baseUrl+"/savings/print-withdraw?account_no="+this.accountDetails.account_no;
    	},
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
		              	width: '400px',
		            }).then(function(result) {
		            	if (result.value) {
		            		vm.pageLoading = true

		            		let accountTransaction = cloneDeep(vm.savingTransactionForm)
		            		accountTransaction.transaction_type = "WITHDRWL"

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
				                        text = "Reference Number " + accountTransaction.reference_number + " already exist."
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

	