<template>
	<div class = "loan-evaluation">
		<div class="box box-info" v-loading = "isLoading">
            <div class="box-header with-border">
              	<h3 class="box-title">Loan Evaluation</h3>
            </div>
	        <div class = "box-body">
	        	<el-row :gutter = "20">
	        		<el-col :span="11">
	        			<div class = "box-content">
				        	<el-form label-position="right" label-width="120px" :model="memberDetails" ref = "loanEvaluateForm">
				        		<el-row :gutter = "20">
				        			<el-col :span="18">
									  	<el-form-item label="Name">
									    	<el-input v-model="memberDetails.fullname" :disabled = "true"></el-input>
									  	</el-form-item>
									</el-col>
				        			<el-col :span="6">
									  	<el-button type = "info" @click="showSearchModal = true">Search Member</el-button>
									</el-col>
				        			<el-col :span="12">
									  	<el-form-item label="ID">
									    	<el-input v-model="memberDetails.id" :disabled = "true"></el-input>
									  	</el-form-item>
									</el-col>
				        			<el-col :span="12">
									  	<el-form-item label="Station">
									    	<el-input v-model="memberDetails.station.name" :disabled = "true"></el-input>
									  	</el-form-item>
									</el-col>
				        			<el-col :span="24">
									  	<el-form-item label="Share Capital">
									    	<el-input v-model="memberDetails.share_capital" :disabled = "true"></el-input>
									  	</el-form-item>
									  	<span v-if = "memberDetails.shareaccount && memberDetails.share_capital == null"> No Share Account</span>
									</el-col>
				        			<el-col :span="24">
									  	<el-form-item label="Maximum Loan Amount">
									    	<el-input v-model="memberDetails.max_loan_amount" :disabled = "true"></el-input>
									  	</el-form-item>
									</el-col>
								</el-row>
							</el-form>
							<hr>
							<div class = "Loan List">
			        			<h4>Member's List of Loan</h4>
								<el-table :data="accountLoanList"height = "350px" stripe border>
						            <el-table-column label="Date Transaction">
						                <template slot-scope="scope">
						                    <span>{{ scope.row.release_date }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Type">
						                <template slot-scope="scope">
						                    <span>{{ scope.row.product.product_name }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Principal Loan">
						                <template slot-scope="scope">
						                    <span>{{ $nf.formatNumber(scope.row.principal, 2) }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Balance">
						                <template slot-scope="scope">
						                    <span>{{ $nf.formatNumber(scope.row.principal_balance, 2) }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Arrears">
						                <template slot-scope="scope">
						                    <span v-if = "scope.row.arrears && scope.row.arrears > 0">{{ $nf.formatNumber(scope.row.arrears, 2)  }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Duration">
						                <template slot-scope="scope">
						                    <span>{{ scope.row.term }}</span>
						                </template>
						            </el-table-column>
						            <!-- <el-table-column label="Maturity Date">
						                <template slot-scope="scope">
						                    <span>{{ scope.row.maturity_date }}</span>
						                </template>
						            </el-table-column -->>
			       				</el-table>
			       			</div>
		       			</div>
					</el-col>
	        		<el-col :span="13">
	        			<div class = "box-content" :class = "disabledBox ? 'disabled-box' : ''">
	        			<!-- <div class = "box-content"> -->
				        	<el-form label-position="right" :model="evaluationForm" :rules = "formRule" ref = "evaluationForm">
				        		<el-row :gutter = "20">
					        		<el-col :span="24">
									  	<el-form-item label="Type" prop = "product_loan_id" label-width="200px">
									  		<el-select v-model="evaluationForm.product_loan_id" filterable placeholder="Select Type" ref = "product_loan_id" @change = "selectLoanProduct" @blur =  "selectLoanProduct" :default-first-option = "true" >
											    <el-option
											      v-for="item in loanProduct"
											      :key="item.id"
											      :label="item.product_name"
											      :value="item.id">
											    </el-option>
										  	</el-select>
									  	</el-form-item>
									  	<el-row :gutter = "20">
									  		<el-col :span="18">
											  	<el-form-item label="Duration" prop = "duration" label-width="200px">
											  		<el-select v-model="evaluationForm.duration" ref = "duration" placeholder="Select duration" :disabled = "disabledBox" @change = "selectLoanDuration">
													    <el-option
													      v-for="item in durationList"
													      :key="item.value"
													      :label="item.label"
													      :value="item.value">
													    </el-option>
													</el-select>
											  		<!-- <el-input-number v-model="evaluationForm.duration" :min = "1" :controls = "false" @keyup.enter.native = "selectLoanDuration" ref = "duration" :disabled = "disabledBox"></el-input-number> -->
											  	</el-form-item>
											</el-col>
									  		<el-col :span="6">
									  			<el-input v-model="evaluationForm.duration_type"  :disabled = "true"></el-input>
									  		</el-col>
										</el-row>
									  	<el-form-item label="Transaction Date" prop = "transaction_date" label-width="200px">
									  		<el-date-picker v-model="evaluationForm.transaction_date" type="date" placeholder="Pick a date"> </el-date-picker>
									  	</el-form-item>
									  	<el-form-item label="Loan Amount" prop = "amount" label-width="200px">
									    	<el-input v-model="evaluationForm.amount" @keyup.enter.native = "enterLoanAmount" ref = "amount" :disabled = "disabledBox">
									    		<el-button slot="append" type = "primary" @click="evaluateLoan(true)">EVALUATE</el-button>
									    	</el-input>
									  	</el-form-item>
									  	<!-- Retention is actually for Share -->
										<el-checkbox @change = "savingsChange" v-model="evaluationForm.is_savings">1% Retention?</el-checkbox>
										<el-row :gutter = "20">
						        			<el-col :span="8">
						        				<el-checkbox v-if = "evaluationForm.product_loan_id  == 20" @change = "changeInterestType" v-model="evaluationForm.interest_on_amount">Deduct Interest on Loan?</el-checkbox>
						        			</el-col>
						        			<el-col :span="8">
												<el-input v-model="dynamicInterest"  placeholder = "Set Interest Rate"></el-input>
						        			</el-col>
						        		</el-row>
										<!-- If misc loan -->
										
										
									</el-col>
									<el-col :span="24">
										<el-row :gutter = "20">
						        			<el-col :span="12">
						        				<h3>Debit</h3>
						        			</el-col>
						        			<el-col :span="12">
						        				<h3>Credit</h3>
						        			</el-col>
						        		</el-row>

						        		<el-row :gutter = "20">
						        			<el-col :span="10">
						        				<el-form-item props = "debit_loan">
										    		<el-input v-model="evaluationForm.debit_loan" :disabled = "true"></el-input>
										  		</el-form-item>
										  	</el-col>
										  	<el-col :span="4">
						        				<p>LOAN</p>
										  	</el-col>
										  	<el-col :span="10">
						        				<el-form-item props = "credit_loan">
										    		<el-input v-model="evaluationForm.credit_loan" :disabled = "true"></el-input>
										  		</el-form-item>
										  	</el-col>
										</el-row>

										<el-row :gutter = "20">
										  	<el-col :span="10">
						        				<el-form-item props = "debit_interest">
										    		<el-input v-model="evaluationForm.debit_interest" :disabled = "true"></el-input>
										  		</el-form-item>
										  	</el-col>
										  	<el-col :span="4">
						        				<p>Interest</p>
										  	</el-col>
										  	<el-col :span="10">
						        				<el-form-item props = "credit_interest">
										    		<el-input v-model="evaluationForm.credit_interest" :disabled = "true"></el-input>
										  		</el-form-item>
										  	</el-col>
										</el-row>

										<el-row :gutter = "20">
											<el-col :span="10">
						        				<el-form-item props = "debit_preinterest">
										    		<el-input v-model="evaluationForm.debit_preinterest" :disabled = "true"></el-input>
										  		</el-form-item>
										  	</el-col>
										  	<el-col :span="4">
						        				<p>Prepaid Int</p>
										  	</el-col>
										  	<el-col :span="10">
						        				<el-form-item props = "credit_preinterest">
										    		<el-input v-model="evaluationForm.credit_preinterest" :disabled = "true"></el-input>
										  		</el-form-item>
										  	</el-col>
										</el-row>

										<el-row :gutter = "20">
											<el-col :span="10">
						        				<el-form-item props = "debit_redemption_ins">
										    		<el-input v-model="evaluationForm.debit_redemption_ins" :disabled = "true"></el-input>
										  		</el-form-item>
										  	</el-col>
										  	<el-col :span="4">
						        				<p>Redemp. Ins.</p>
										  	</el-col>
										  	<el-col :span="10">
						        				<el-form-item props = "credit_redemption_ins">
										    		<el-input v-model="evaluationForm.credit_redemption_ins" :disabled = "true"></el-input>
										  		</el-form-item>
										  	</el-col>
										</el-row>
										
										<el-row :gutter = "20">
										  	<el-col :span="10">
										  		<p>&nbsp;</p>
										  	</el-col>
										  	<el-col :span="4">
						        				<p>Service Charge</p>
										  	</el-col>
										  	<el-col :span="10">
						        				<el-form-item props = "service_charge_amount">
										    		<el-input v-model="evaluationForm.service_charge_amount" :disabled = "true"></el-input>
										  		</el-form-item>
										  	</el-col>
										</el-row>

										<el-row :gutter = "20">
										  	<el-col :span="10">
										  		<p>&nbsp;</p>
										  	</el-col>
										  	<el-col :span="4">
						        				<p>Retention</p>
										  	</el-col>
										  	<el-col :span="10">
						        				<el-form-item props = "savings_retention">
										    		<el-input v-model="evaluationForm.savings_retention" :disabled = "true"></el-input>
										  		</el-form-item>
										  	</el-col>
										</el-row>


										<el-row :gutter = "20">
										  	<el-col :span="10">
										  		<p>&nbsp;</p>
										  	</el-col>
										  	<el-col :span="4">
						        				<p>Notary</p>
										  	</el-col>
										  	<el-col :span="10">
						        				<el-form-item props = "notary_amount">
										    		<el-input v-model="evaluationForm.notary_amount" :disabled = "true"></el-input>
										  		</el-form-item>
										  	</el-col>
										</el-row>

										<el-row :gutter = "20">
										  	<el-col :span="10">
										  		<p>&nbsp;</p>
										  	</el-col>
										  	<el-col :span="4">
						        				<p><b>NET CASH</b></p>
										  	</el-col>
										  	<el-col :span="10">
						        				<el-form-item props = "net_cash">
										    		<el-input v-model="evaluationForm.net_cash" :disabled = "true"></el-input>
										  		</el-form-item>
										  	</el-col>
										</el-row>
										<hr>
										<el-row :gutter = "20">
										  	<el-col :span="10">
										  		<el-form-item props = "debit_total">
										    		<el-input v-model="evaluationForm.debit_total" :disabled = "true"></el-input>
										  		</el-form-item>
										  	</el-col>
										  	<el-col :span="4">
						        				<p>TOTAL</p>
										  	</el-col>
										  	<el-col :span="10">
						        				<el-form-item props = "credit_total">
										    		<el-input v-model="evaluationForm.credit_total" :disabled = "true"></el-input>
										  		</el-form-item>
										  	</el-col>
										</el-row>
										<hr>
										<el-row :gutter = "20">
											 <el-col :span="24">
											  	<el-form-item label="Principal Amortization / quincena" prop = "principal_amortization_quincena" label-width="300px">
											    	<el-input v-model="evaluationForm.principal_amortization_quincena" ref = principal_amortization_quincena :disabled = "true"></el-input>
											  	</el-form-item>
											  	<el-form-item label="Prepaid Amortization / quincena" prop = "prepaid_amortization_quincena" label-width="300px">
											    	<el-input v-model="evaluationForm.prepaid_amortization_quincena" ref = prepaid_amortization_quincena :disabled = "true"></el-input>
											  	</el-form-item>
											</el-col>
										</el-row>
									</el-col>
								</el-row>
				        	</el-form>
				        	<div style = "margin-top: 10px;">
		       					<el-button @click = "printLoan()" ref = "printLoan">Print</el-button> 
		       					<el-button type = "primary" @click = "newLoan()" ref = "newLoan">Verify</el-button> 
							

		       				</div>
				        </div>	        			
	        		</el-col>
				</el-row>
			</div>
		</div>
		<search-member :show-modal = "showSearchModal" :data-includes = "['shareaccount']" @select="populateField" @close = "showSearchModal = false" >
	  	</search-member>

	  	<dialog-modal 
	  		title-header = ""
	  		width = "80%"
            v-if="showFormModal"
            :visible.sync="showFormModal"
            @close="showFormModal = false">
            <loan-evaluation-form
            	:page-data = "loanEvalFormData"
            	:to-print = "true"
            	>
            </loan-evaluation-form>
        </dialog-modal>
	</div>
</template>

<script>
	window.noty = require('noty')
    import Noty from 'noty'
    import cloneDeep from 'lodash/cloneDeep'    
    import _forEach from 'lodash/forEach'

	import {dialogComponent} from '../../mixins/dialogComponent.js'
	import _message from '../../mixins/messageDialog.js'

export default {
	props: ['dataLoanProduct', 'dataDefaultSettings'],
	mixins: [dialogComponent],
	data: function () {
		let form = {product_loan_id : null, duration : null, duration_type : "Months", amount : null, service_charge : true, is_savings : false, savings_retention: null, transaction_date : null, interest_on_amount : false}
		let durationList = [ {value : 6, label : 6},
			{value : 12, label : 12},
			{value : 24, label : 24},
			{value : 36, label : 36},
		]
		return {
			evaluationForm 			: form,
			loanProduct 			: this.dataLoanProduct,
			memberDetails			: {id : null, fullname : null, station : {}},
			showSearchModal			: false,
			accountLoanList 		: [],
			loanDuration 			: null,
			formRule 				: [],
			minTerm 				: 1,
			maxTerm					: 12,
			disabledBox 			: true,
			isLoading				: false,
			LoanToRenew				: null,
			durationList 			:  durationList,
			showFormModal 			: false,
			loanEvalFormData 		: {},
			loanTransaction 		: [],
			dynamicInterest 		: null,
		}
	},
	created(){

		this.evaluationForm.product_loan_id = this.loanProduct[0].id
		this.evaluationForm.transaction_date = this.$systemDate
		var validateDuration = (rule, value, callback) => {

    		let getProduct = this.loanProduct.find(pr =>{
    			return Number(pr.id) == Number(this.evaluationForm.product_loan_id)
    		})

    		if(getProduct){
    			if(Number(value) < Number(getProduct.term_min)){
    				callback(new Error("Term is below minimum allowed loan term! " +getProduct.product_name + " is " +getProduct.term_min + " to " +getProduct.term_max + " months."));
    			}
    			else if(Number(value) > Number(getProduct.term_max)){
    				callback(new Error("Term is above maximum allowed loan term! " +getProduct.product_name + " is " +getProduct.term_min + " to " +getProduct.term_max + " months."));
    			}
    		}
    		callback();
	    };

		var validateAmount = (rule, value, callback) => {

    		let getProduct = this.loanProduct.find(pr =>{
    			return Number(pr.id) == Number(this.evaluationForm.product_loan_id)
    		})

    		if(getProduct){
    			if(Number(value) < Number(getProduct.min_amount) || Number(value) > Number(getProduct.max_amount)){
    				callback(new Error(getProduct.product_name + " minimum amount is " + getProduct.min_amount + " and maximum amount is " + getProduct.max_amount));
    			}
    		}
    		callback();
	    };

		this.formRule = {
  			product_loan_id : [{ required: true, message: 'Product cannot be blank.', trigger: 'change' },],
  			duration : [{ required: true, message: 'Term duration cannot be blank.', trigger: 'blur' },
  				{ validator: validateDuration, trigger: 'blur', trigger: 'blur' },],
  			amount : [{ required: true, message: 'Amount cannot be blank.', trigger: 'blur' },
  				{ validator: validateAmount, trigger: 'blur', trigger: 'blur' },],
		}

		this.$EventDispatcher.listen('CLOSE_LOAN_EVALUATION_FORM', data => {
			this.showFormModal = false 
		})
	},
	methods:{		
		printLoan(){
			if(this.evaluationForm.product_loan_id == null || this.evaluationForm.product_loan_id == "")
				return

			let getLoan = this.loanProduct.find(rs => {return Number(rs.id) == Number(this.evaluationForm.product_loan_id)})
			this.loanEvalFormData = {
				evaluationForm : this.evaluationForm, 
				memberDetails : this.memberDetails, 
				shareDetails : this.memberDetails.shareaccount ? this.memberDetails.shareaccount : null, 
				latestLoan : this.LoanToRenew,
				loanProduct : getLoan,
				otherLoans : this.accountLoanList,
				loanTransaction : this.loanTransaction,
			}
			//this.$htmlToPaper('printMe');
			setTimeout(() => {
                this.showFormModal = true
            }, 500);
			
		},
		savingsChange(value){
			console.log("value", value)
			this.evaluateLoan()
		},
		changeInterestType(){
			this.evaluateLoan()
		},
    	populateField(data){
    		this.resetEvaluationForm()
    		this.memberDetails = data
    		this.memberDetails.share_capital = null
    		this.memberDetails.max_loan_amount = 0
    		if(data.shareaccount != null){
    			this.memberDetails.share_capital = data.shareaccount.balance
    			this.memberDetails.max_loan_amount = parseFloat(this.memberDetails.share_capital) * 4
    		}
    		this.disabledBox = false
    		this.$refs.product_loan_id.focus() 
    		this.getAccounLoanInfo(this.memberDetails.id)
    		/*console.log(this.$refs.newLoan)
    		this.$refs.newLoan.focus()*/
    	},
    	selectLoanProduct(val){
    		console.log("Heresss", val)
    		this.$refs.duration.focus()

    	},
    	selectLoanDuration(){
    		this.$refs.amount.focus()
    	},
    	getAccounLoanInfo(member_id){
    		console.log()
    		this.$API.Loan.getAccounLoanInfo(member_id)
    		.then(result => {
    			let res = result.data
    			console.log("res", res)
    			this.accountLoanList = res
			}).catch(err => {
				console.log(err)
			})
    	},
    	getProduct(product_id){
    		return this.loanProduct.find(lp => { return Number(lp.id) == Number(product_id) })
    	},
    	changeServiceCharge(value){
    		if(value){
    			this.evaluateLoan()
    		}
    	},
    	changeRedemptionInsurance(value){
    		if(value){
    			this.evaluateLoan()
    		}
    	},
    	changeSavingRetention(value){
    		if(value){
    			this.evaluateLoan()
    		}
    	},
    	resetEvaluationForm(){
    		this.$refs['evaluationForm'].resetFields();
    	},
    	getLatestLoan(loan_id, member_id){
    		let date_trans = this.$df.formatDate(this.evaluationForm.transaction_date)
    		this.$API.Loan.getLatestLoan(loan_id, member_id, date_trans)
    		.then(result => {
    			let res = result.data
    			console.log("res", res)
			}).catch(err => {
				console.log(err)
			})
    	},
    	enterLoanAmount(){
    		console.log("Eva")
    		this.$refs.evaluationForm.validateField('amount', valid => {
    			console.log(valid, valid)
    			if (valid) {
    				
    			}
    			else{
    				this.evaluationForm.loan_amount = this.evaluationForm.amount
    				this.evaluateLoan(true)
    			}
    		})
    	},
    	evaluateLoan(first = false){    		
    		let vm = this	

    		this.$refs.evaluationForm.validate((valid) => {
    			if (valid) {
    		
		    		if(this.evaluationForm.product_loan_id && this.evaluationForm.duration && this.evaluationForm.amount){

			    		if(first){
			    			if((Number(this.evaluationForm.product_loan_id) == 1 || Number(this.evaluationForm.product_loan_id) == 2)){
				    			this.evaluationForm.is_savings = true
			    			}
			    			else{
			    				this.evaluationForm.is_savings = false
			    			}
			    		}

		    		//	console.log("Calculate")
		    			let getProduct = this.loanProduct.find(lp => { return Number(lp.id) == Number(this.evaluationForm.product_loan_id) })
		    			let amount = Number(this.evaluationForm.amount)
		    			let duration = this.evaluationForm.duration
		    			let transaction_date = this.evaluationForm.transaction_date ? this.$df.formatDate(this.evaluationForm.transaction_date, 'YYYY-MM-DD') : this.$systemDate


		    			console.log("getProduct", getProduct)
		    			this.isLoading = true
		    			if(getProduct){
		    				this.$API.Loan.getLatestLoan(getProduct.id, this.memberDetails.id, transaction_date)
				    		.then(result => {
				    			let res = result.data
								console.log("resRES", res)
								vm.LoanToRenew = res.data.latestLoan;
								vm.loanTransaction = res.data.loanTransaction;
				    			this.calculateLoan(getProduct, res.data)
				    			
				    			this.isLoading = false
							}).catch(err => {
								console.log(err)
								this.isLoading = false
							})
			    			
			    		}

		    		}
		    	}
	          	else {

					this.clearFields()
	            	return false;
	          	}
    		})
		},
		
		clearFields()
		{
			this.evaluationForm.debit_interest = '';
			this.evaluationForm.credit_interest = '';
			this.evaluationForm.debit_preinterest = '';
			this.evaluationForm.credit_preinterest = '';
			

    		


			this.evaluationForm.prepaid_amortization_quincena = ''
			this.evaluationForm.service_charge_amount = ''
			//this.evaluationForm.saving_retention_amount = saving_retention
			//this.evaluationForm.redemption_insurance_amount = ''
			this.evaluationForm
			this.evaluationForm.principal_amortization_quincena = ''
			this.evaluationForm.prepaid_amortization_quincena = ''

		},
		/*
		This prepaid calculation is for quincena
		*/
		calculatePrepaidInterest(principal, term, prepaid_int, loantype)
		{	let prepaid = 0

			//NOTICE: Result of this function implies prepaid interest per quincena. i repeat, per quincena.

			//*---* = July 24, 2020 : New loan policy updates. Check documentation

			/*
			/*---*
			if(loantype==1)
			{
				prepaid  = principal * prepaid_int;
				prepaid = prepaid / 2;
			}*/

			if(loantype==2)
			{
				prepaid = (principal * prepaid_int) / 12;
				prepaid = prepaid * 12;
				prepaid = prepaid / (term * 2);

				/* 
				*---*
				prepaid = (principal * prepaid_int) / 10;
				prepaid = prepaid * 14;
				prepaid = prepaid / (term * 2);
				*/

				/*if(term<=12)
				{
					prepaid = (principal * prepaid_int) / 5;
					prepaid = prepaid * 7;
					prepaid = prepaid / 24;
				}

				else if(term<=18)
				{
					prepaid = (principal * prepaid_int) / 7.5;
					prepaid = prepaid * 11.5;
					prepaid = prepaid / 36;
				}

				else if(term<=24)
				{
					prepaid = (principal * prepaid_int) / 10;
					prepaid = prepaid * 14;
					prepaid = prepaid / 48;
				}

				else if(term<=36)
				{
					prepaid = (principal * prepaid_int) / 15;
					prepaid = prepaid * 21;
					prepaid = prepaid / 72;
				}*/
			}


			return Number(prepaid).toFixed(2);


		},
		/*
		Prepaid calculation on actual loan
		*/
		calculateMiscDeductions(getProduct, evalForm)
		{

			//*---* = July 24, 2020 : New loan policy updates. Check documentation

			/*

				1. Interest type id 1 is the normal credit prepaid interest

				---start of doing consideration #1---
			*/

			if(Number(getProduct.interest_type_id) == 1)
			{
				this.evaluationForm.credit_preinterest = parseFloat(Number(this.evaluationForm.amount) * Number(getProduct.prepaid_interest)).toFixed(2) ;
				this.evaluationForm.debit_loan = parseFloat(this.evaluationForm.amount).toFixed(2);

			}

			/*
				----- end of doing consideration #1 ------

				2. Interest type id 2 means the prepaid is add in to the debit loan

				----- start of doing consideration #2 ------
			*/
			
			else if(Number(getProduct.interest_type_id) ==2) //2 stands for add in
			{
			  	let principal_amt = this.evaluationForm.amount
			  	this.evaluationForm.credit_preinterest = parseFloat(principal_amt * getProduct.prepaid_interest).toFixed(2);
			  	this.evaluationForm.debit_loan = Number(principal_amt) + Number(this.evaluationForm.credit_preinterest);

			  	if(this.evaluationForm.interest_on_amount){
			  		let int = getProduct.prepaid_interest
			  		if(this.dynamicInterest){
			  			int = Number(this.dynamicInterest) / 100
			  		}
			  		this.evaluationForm.credit_preinterest = parseFloat(principal_amt * int).toFixed(2);
			  		this.evaluationForm.debit_loan = Number(principal_amt);
			  	}
			}
			/*
				----- end of doing consideration #1 ------
			*/


			/*

				1. is loan eGadget Loan nga samok kaayo?

				---start of doing consideration #1---
			*/

			/*if(Number(getProduct.id) == 14 || getProduct.product_name.includes("GADGET"))
			{
				if(evalForm.duration>=24)
				{
				 	let accumulated_prepaid =  parseFloat((evalForm.amount * getProduct.prepaid_interest) * (evalForm.duration/12)).toFixed(2);
				 	let eddedToPrincipal = accumulated_prepaid / (evalForm.duration/12); //assuming loan is 2 years
				 	this.evaluationForm.credit_preinterest = parseFloat(accumulated_prepaid).toFixed(2);
				 	this.evaluationForm.debit_loan = parseFloat(Number(evalForm.amount) + Number(eddedToPrincipal)).toFixed(2);


				}

				else
				{
					 let principal_amt = this.evaluationForm.amount
					 this.evaluationForm.credit_preinterest = parseFloat(principal_amt * getProduct.prepaid_interest).toFixed(2);
					 this.evaluationForm.debit_loan = Number(principal_amt) + Number(this.evaluationForm.credit_preinterest);
		 
				}

				
			}*/

			/*
				----- end of doing consideration #1 ------

				2. is Buy out Loan nga samok kaayo?
				annual ang addon interest.

				---start of doing consideration #2---
			*/
			/*else if(Number(getProduct.id) == 12 || getProduct.product_name.includes("BUY-OUT"))
			{
				console.log('BUY-OUT', evalForm)
				let accumulated_prepaid = parseFloat(evalForm.amount * getProduct.prepaid_interest).toFixed(2)
				if(evalForm.duration>=24)
				{
				 	accumulated_prepaid =  parseFloat((evalForm.amount * getProduct.prepaid_interest) * (evalForm.duration/12)).toFixed(2);
				}

				let principal_amt = this.evaluationForm.amount
				this.evaluationForm.credit_preinterest = accumulated_prepaid;
				this.evaluationForm.debit_loan = Number(principal_amt) + Number(this.evaluationForm.credit_preinterest);
				
			}*/
			/*
				----- end of doing consideration #2 ------
				regardless if loan is latest or not: consider these factors
				3. is loan product add in or prepaid something?

				----- start of doing consideration #3 ------
			*/
			
			/*else if(Number(getProduct.interest_type_id) ==2) //2 stands for add in
			{
				console.log("add in");
			  	let principal_amt = this.evaluationForm.amount
			  	this.evaluationForm.credit_preinterest = parseFloat(principal_amt * getProduct.prepaid_interest).toFixed(2);
			  	this.evaluationForm.debit_loan = Number(principal_amt) + Number(this.evaluationForm.credit_preinterest);
			  	console.log("prep int yohooa");

			}*/

			/*
				----- end of doing consideration #2 ------

				3. is loan regular loan

				---start of doing consideration #3---
			*/

			/*else if(Number(getProduct.id) == 2)
			{
				this.evaluationForm.credit_preinterest = parseFloat(Number(this.evaluationForm.amount) * Number(getProduct.prepaid_interest)).toFixed(2) ;
				this.evaluationForm.debit_loan = parseFloat(this.evaluationForm.amount).toFixed(2);

			}*/

			/*
				---end of doing consideration #3---

			*/

			/*
				---Others---

			*/
			/*else
			{
				this.evaluationForm.credit_preinterest = 0 ;
				this.evaluationForm.debit_loan = parseFloat(this.evaluationForm.amount).toFixed(2);

			}*/

		},
		calculateServiceCharge(getProduct, evalForm){
			let dbtLoan = cloneDeep(this.evaluationForm.debit_loan)
			let crdtLoan = cloneDeep(this.evaluationForm.credit_loan)

			let amountFee = Number(dbtLoan)
			if(getProduct.id == 2){ //If regular loan, get service charge on debit and credit loan differene
				amountFee = Number(dbtLoan) - Number(crdtLoan)
			}

			let srvCharge = 0

			console.log("getProduct", getProduct, evalForm, amountFee)
			if(amountFee > 0 && evalForm.service_charge){

				//From last calculation of service fee. Just don't delete just in case
    			/*let getServiceFee = getProduct.serviceCharge.find(sc => { 
    				if(sc.month_term && sc.min_amount && sc.max_amount){
    					return Number(sc.month_term) == Number(evalForm.duration) && Number(evalForm.amount) >= Number(sc.min_amount) && Number(evalForm.amount) <= Number(sc.max_amount)
    				}
    				return false
    				
    			})*/
    			let getServiceFee = null
    			if(getProduct.serviceCharge.length > 0){
    				getServiceFee = getProduct.serviceCharge[0]
    			}

    			console.log("getServiceFee", getServiceFee, amountFee)
				if(getServiceFee){
    				srvCharge = amountFee * (Number(getServiceFee.percentage) / 100)
    				console.log("srvCharge", srvCharge, getServiceFee.percentage)
    			}

			}
			return srvCharge
		},
    	calculateLoan(getProduct, dataneeded){
			let latestLoan = dataneeded.latestLoan;
			let lastTran = dataneeded.lastTransaction;

			let evalForm = cloneDeep(this.evaluationForm)
			let service_charge = 0	

			let transaction_date = this.evaluationForm.transaction_date ? this.$df.formatDate(this.evaluationForm.transaction_date, 'YYYY-MM-DD') : this.$systemDate

			this.evaluationForm.debit_loan = parseFloat(this.evaluationForm.amount).toFixed(2)
			if(!latestLoan)
			{
				//this.evaluationForm.savings_retention = this.evaluationForm.is_savings ? parseFloat(Number(this.evaluationForm.amount) * 0.01).toFixed(2) : 0 ;
    			this.evaluationForm.credit_loan = parseFloat(0).toFixed(2)
				this.evaluationForm.debit_redemption_ins = parseFloat(0).toFixed(2)
				this.evaluationForm.debit_interest = 0;
				this.evaluationForm.credit_interest = 0;
				this.evaluationForm.debit_preinterest = 0;

			}
			else{
				console.log("lastTran.amount_balance", lastTran.amount_balance)
    			this.evaluationForm.credit_loan = parseFloat(lastTran.amount_balance).toFixed(2)

    			let calVersion = this.$ch.checkVersion(latestLoan.release_date)
    			console.log("calVersion", calVersion)

    			/* CREDIT INTEREST -START- */
    			let credit_interest = 0
    			if(getProduct.id == 2 || (calVersion == "1" && getProduct.id == 1)){ //2: Regular Loan, 1 Appliance Loan
    				credit_interest = parseFloat(lastTran.interest_accum).toFixed(2);
    			}
    			else{
    				console.log('"HERE')
    				credit_interest = this.getInterestLoan(lastTran.total_amount_paid, latestLoan, getProduct)
    			}
				this.evaluationForm.credit_interest = parseFloat(credit_interest).toFixed(2);
				/* CREDIT INTEREST -END- */

				//If has existing account for the loan product
				let daystart = moment(transaction_date, "YYYY-MM-DD");
				let dayend = moment(lastTran.last_tran_date, "YYYY-MM-DD"); //usd against the latest TRANSACTION.
				let monthend = moment(latestLoan.release_date, "YYYY-MM-DD"); //used against the latest loan.
				let rangeNoOfDays = moment.duration(daystart.diff(dayend)).asDays(); 
				let rangeNoOfMonths = moment.duration(daystart.diff(monthend)).asMonths(); //to be used for calculating unused redemption insurance. // 7
				rangeNoOfMonths = Math.round(rangeNoOfMonths) // the downward nearest integer

				/* DEBIT REDEMPTION INSURANCE -START- */
				let unusedRedemption = latestLoan.term - rangeNoOfMonths
				let redemptionInsuranceDebit = 0
				if(unusedRedemption>=1)
				{
					redemptionInsuranceDebit = (latestLoan.redemption_insurance / latestLoan.term) * unusedRedemption
				}
				this.evaluationForm.debit_redemption_ins = parseFloat(redemptionInsuranceDebit).toFixed(2)
				/* DEBIT REDEMPTION INSURANCE -END- */

				/* DEBIT PREPAID INTEREST -START- */
				let debit_prepaid = lastTran.prepaid_interest==null ? 0 :  lastTran.prepaid_interest;
				if(getProduct.id == 2 || (calVersion == "1" && getProduct.id == 1)){ //2: Regular Loan, 1 Appliance Loan
    				debit_prepaid = lastTran.prepaid_interest==null ? 0 :  lastTran.prepaid_interest;
    			}
    			else{
    				debit_prepaid = this.getPrepaidDebit(lastTran.balance_after_cutoff, latestLoan, getProduct)
    			}

				this.evaluationForm.debit_preinterest = parseFloat(debit_prepaid).toFixed(2); 

				/* DEBIT PREPAID INTEREST -END- */
			}
			this.evaluationForm.debit_interest = 0;

    		/* CREDIT REDEMPTION INSURRANCE -START- */
    		//let redemptionInsurance = parseFloat(this.evaluationForm.amount * getProduct.redemption_insurance) * (parseFloat(evalForm.duration)/12)
    		let redemptionInsurance = 0
    		if(Number(getProduct.redemption_insurance) == 1){
    			redemptionInsurance = this.calculateRedemptionInsurance(this.evaluationForm.amount, evalForm.duration)
    		} 
			this.evaluationForm.credit_redemption_ins = parseFloat(redemptionInsurance).toFixed(2)
    		/* CREDIT REDEMPTION INSURRANCE -END- */
	

			/* CREDIT NOTARY FEE -START- */
			this.evaluationForm.notary_amount = getProduct.notary_fee
			/* CREDIT NOTARY FEE -END- */


			/* CREDIT SERVICE CHARG -START- */
			service_charge = this.calculateServiceCharge(getProduct, evalForm) 
			this.evaluationForm.service_charge_amount = parseFloat(service_charge).toFixed(2)
			/* CREDIT SERVICE CHARG -END- */

			/* CREDIT PREPAID INTEREST -START- */
			this.calculateMiscDeductions(getProduct, evalForm);
			/* CREDIT PREPAID INTEREST -END- */

			this.evaluationForm.debit_total = parseFloat(Number(this.evaluationForm.debit_loan) + Number(this.evaluationForm.debit_preinterest) + Number(this.evaluationForm.debit_redemption_ins)).toFixed(2)
						
			this.evaluationForm.net_cash = parseFloat(Number(this.evaluationForm.debit_total) - (Number(this.evaluationForm.credit_loan) + Number(this.evaluationForm.credit_interest) + Number(this.evaluationForm.credit_preinterest) + Number(this.evaluationForm.credit_redemption_ins) + Number(this.evaluationForm.service_charge_amount) +  Number(this.evaluationForm.notary_amount))).toFixed(2);

			/* CREDIT SAVING RETENTION -START- */
			this.evaluationForm.savings_retention = this.evaluationForm.is_savings ? parseFloat(Number(this.evaluationForm.net_cash) * 0.01).toFixed(2) : 0 ;
			/* CREDIT SAVING RETENTION -END- */

			this.evaluationForm.net_cash = parseFloat(this.evaluationForm.net_cash - this.evaluationForm.savings_retention).toFixed(2);

			this.evaluationForm.credit_total = parseFloat(Number(this.evaluationForm.credit_loan) + Number(this.evaluationForm.credit_interest) + Number(this.evaluationForm.credit_preinterest) + Number(this.evaluationForm.credit_redemption_ins) + Number(this.evaluationForm.service_charge_amount) + Number(this.evaluationForm.savings_retention) +  Number(this.evaluationForm.notary_amount) + Number(this.evaluationForm.net_cash)).toFixed(2)

			this.evaluationForm.member_id = this.memberDetails.id

			let principal_amortization_quincena = parseFloat(this.evaluationForm.debit_loan)/ parseFloat(evalForm.duration * 2)
			this.evaluationForm.principal_amortization_quincena = Number(principal_amortization_quincena).toFixed(2)
			

			this.evaluationForm.prepaid_amortization_quincena = this.calculatePrepaidInterest(Number(this.evaluationForm.amount), Number(this.evaluationForm.duration), getProduct.prepaid_monthly_interest, getProduct.id);
			//this.evaluationForm.principal_amortization_quincena = 100
			//this.evaluationForm.prepaid_amortization_quincena = 250
			
    	}, 
    	/*
    	This is just given to me. I'm not really sure why the calculation is like this.
    	Some numbers are just static.
    	*/
    	calculateRedemptionInsurance(principal, term){
    		console.log('calculateRedemptionInsurance', principal, term)
    		let redemption = (parseFloat(principal) / 1000)  * 0.6 
    		redemption = redemption * parseFloat(term)

    		return redemption

    	},
    	/*
    	Interest before August 2020
    	*/
    	getInterestLoan(totalPaid, account, product){
    		let amt = 0
    		let calVersion = this.$ch.checkVersion(account.release_date)
    		console.log("getInterestLoan", calVersion, totalPaid, account, product)

    		if(calVersion == '1-2020.08'){ // //New policy update from August 2020
    			amt = totalPaid * parseFloat(account.prepaid_int)
    		}
    		else{
    			if([3,5,6,8,12,14].indexOf(Number(product.id)) >= 0){
	    			amt = totalPaid * parseFloat(account.prepaid_int) / (1 + parseFloat(account.prepaid_int))
	    		}
	    		else{
	    			amt = totalPaid * parseFloat(account.prepaid_int)
	    		}
    		}

    		return amt
    		
    	},
    	/*
    	Prepaid before August 2020
    	*/
    	getPrepaidDebit(balanceCutOff, account, product){
    		let amt = 0
    		if(product.interest_type_id == 1){

	    		let calVersion = this.$ch.checkVersion(account.release_date)
				console.log("'balanceCutOff'", balanceCutOff, account, product, calVersion)

	    		if(calVersion == '1-2020.08'){ // //New policy update from August 2020
	    			if(product.id != 1){
		    			amt = balanceCutOff * parseFloat(account.prepaid_int)
		    		} 
	    		}
	    		else{
	    			console.log("'balanceCutOff'", balanceCutOff)
	    			if([3,5,6,8,12,14].indexOf(Number(product.id)) >= 0){
		    			amt = balanceCutOff * parseFloat(account.prepaid_int) / (1 + parseFloat(account.prepaid_int))
		    		}
		    		else{
		    			amt = balanceCutOff * parseFloat(account.prepaid_int)
		    		}
	    		}
	    	}

    		return amt
    		
    	},
    	newLoan(){
    		if(this.memberDetails.id != null){

    			this.$swal({
	                title: 'Apply Loan',
	                text: "Are you sure you want to apply loan?",
	                type: 'warning',
	                showCancelButton: true,
	                cancelButtonColor: '#d33',
	                confirmButtonText: 'Proceed',
	                focusConfirm: false,
	                focusCancel: true,
	                cancelButtonText: 'Cancel',
	                reverseButtons: true,
	                customClass: {
	                    container: 'loan-product-form-swal'
	                }
	            }).then(result => {
	            	if (result.value) {
		    			this.disabledBox = false

		    			console.log("this.evaluationForm", this.evaluationForm)
		    			let formEval = cloneDeep(this.evaluationForm)
		    			formEval['transaction_date'] = this.$df.formatDate(formEval['transaction_date'], "YYYY-MM-DD")
						let loandata = {
							evaluationFormss: formEval,
							loanToRenew: this.LoanToRenew == null ? null : {
								account_number: this.LoanToRenew.account_no,
								product_id:  this.LoanToRenew.loan_id,
							}
						}

						this.isLoading = true
						
						this.$API.Loan.applyLoan(loandata)
						.then(result=>{
							console.log("successresult", result.data);
							let res = result.data
							if(!res.success){
								let text = 'New loan not applied. Please try again or contact administrator.'
								if(res.status == 'has_verified'){
									text = 'New loan not applied. Already has existing verified loan. Please cancel it first.'
								}

								new Noty({
			                        theme: 'relax',
			                        type: 'error',
			                        layout: 'topRight',
			                        text: text,
			                        timeout: 5000
			                    }).show();
							}
							else{
								new Noty({
			                        theme: 'relax',
			                        type: 'success',
			                        layout: 'topRight',
			                        text: "New loan successfully applied. See 'Pending List'",
			                        timeout: 5000
			                    }).show();
			                    location.reload();
							}
							

						}).catch(err=>{
							console.log("apierror", err.message);
							new Noty({
		                        theme: 'relax',
		                        type: 'error',
		                        layout: 'topRight',
		                        text: 'An error occured. Please try again or contact administrator',
		                        timeout: 5000
		                    }).show();
						})
						.then(_ => { 
							this.isLoading = false
						})
					}
				})
    		} 
    		else{
    			new Noty({
	                theme: 'relax',
	                type: 'error' ,
	                layout: 'topRight',
	                text: 'Please select member first',
	                timeout: 2500
	            }).show();
    		}	
    	}
	}
}
</script>
<style lang="scss">
  	@import '../../assets/site.scss';
  	@import '~noty/src/noty.scss';

  	.loan-evaluation{
  		.box{
  			h3{
	  			margin-top: 5px;
	  		}
	  		.el-form-item{
			    margin-bottom: 0px !important;
			}

			.debit-credit-content{
				p.loan-label{
					text-align: center;
					font-weight: bold
				}
			}
  		}
  		.disabled-box{
  			opacity: 0.3;
  		}
  		.el-form-item__error{
  			position: relative;
  		}

		
	}
</style>