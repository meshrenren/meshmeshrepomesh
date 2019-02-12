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
								</el-row>
							</el-form>
							<hr>
							<div class = "Loan List">
			        			<h4>Member's List of Loan</h4>
								<el-table :data="accountLoanList" style="width: 100%" height = "350px" stripe border>
						            <el-table-column label="Date Transaction">
						                <template slot-scope="scope">
						                    <span style="margin-left: 10px">{{ scope.row.release_date }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Type">
						                <template slot-scope="scope">
						                    <span style="margin-left: 10px">{{ scope.row.product.product_name }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Principal Loan">
						                <template slot-scope="scope">
						                    <span style="margin-left: 10px">{{ parseFloat(scope.row.principal).toFixed(2) }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Balance">
						                <template slot-scope="scope">
						                    <span style="margin-left: 10px">{{ parseFloat(scope.row.principal_balance).toFixed(2)  }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Duration">
						                <template slot-scope="scope">
						                    <span style="margin-left: 10px">{{ scope.row.term }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Maturity Date">
						                <template slot-scope="scope">
						                    <span style="margin-left: 10px">{{ scope.row.maturity_date }}</span>
						                </template>
						            </el-table-column>
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
											  		<el-input-number v-model="evaluationForm.duration" :min = "1" :controls = "false" @keyup.enter.native = "selectLoanDuration" ref = "duration" :disabled = "disabledBox"></el-input-number>
											  	</el-form-item>
											</el-col>
									  		<el-col :span="6">
									  			<el-input v-model="evaluationForm.duration_type"  :disabled = "true"></el-input>
									  		</el-col>
										</el-row>
									  	<el-form-item label="Loan Amount" prop = "amount" label-width="200px">
									    	<el-input v-model="evaluationForm.amount" @keyup.enter.native = "enterLoanAmount" ref = "amount" :disabled = "disabledBox">
									    		<el-button slot="append" type = "primary" @click="evaluateLoan()">EVALUATE</el-button>
									    	</el-input>
									  	</el-form-item>
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
						        				<el-form-item props = "notary_amount">
										    		<el-input v-model="evaluationForm.net_cah" :disabled = "true"></el-input>
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
		       					<el-button type = "primary" @click = "newLoan()" ref = "newLoan">New Loan</el-button> 
		       				</div>
				        </div>	        			
	        		</el-col>
				</el-row>
			</div>
		</div>
		<search-member :show-modal = "showSearchModal" :data-includes = "['shareaccount']" @select="populateField" @close = "showSearchModal = false" >
	  	</search-member>
	</div>
</template>

<script>
	window.noty = require('noty');
    import axios from 'axios'
    import Noty from 'noty'
    import cloneDeep from 'lodash/cloneDeep'    
    import _forEach from 'lodash/forEach'

export default {
	props: ['dataLoanProduct', 'dataDefaultSettings'],
	data: function () {
		let form = {product_loan_id : null, duration : null, duration_type : "Months", amount : null, service_charge : true}
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
			isLoading				: false
		}
	},
	created(){
		this.evaluationForm.product_loan_id = this.loanProduct[0].id
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
	},
	methods:{		
    	populateField(data){
    		this.memberDetails = data
    		this.memberDetails.share_capital = null
    		if(data.shareaccount != null){
    			this.memberDetails.share_capital = data.shareaccount.balance
    		}
    		this.disabledBox = false
    		this.$refs.product_loan_id.focus() 
    		this.getAccounLoanInfo(this.memberDetails.id)
    		/*console.log(this.$refs.newLoan)
    		this.$refs.newLoan.focus()*/
    	},
    	selectLoanProduct(val){
    		console.log("Here", val)
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

    	},
    	getLatestLoan(loan_id, member_id){
    		console.log("Here")
    		this.$API.Loan.getLatestLoan(loan_id, member_id)
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
    			if (valid) {
    				console.log("Has Error")
    			}
    			else{
    				console.log("Valid Amount")
    				this.evaluationForm.loan_amount = this.evaluationForm.amount
    				this.evaluateLoan()
    			}
    		})
    	},
    	evaluateLoan(){    		
    		let vm = this	

    		this.$refs.evaluationForm.validate((valid) => {
    			if (valid) {
    		
		    		if(this.evaluationForm.product_loan_id && this.evaluationForm.duration && this.evaluationForm.amount){


		    			console.log("Calculate")
		    			let getProduct = this.loanProduct.find(lp => { return Number(lp.id) == Number(this.evaluationForm.product_loan_id) })
		    			let amount = Number(this.evaluationForm.amount)
		    			let duration = this.evaluationForm.duration


		    			console.log("getProduct", getProduct)
		    			this.isLoading = true
		    			if(getProduct){
		    				this.$API.Loan.getLatestLoan(getProduct.id, this.memberDetails.id)
				    		.then(result => {
				    			let res = result.data

				    			this.calculateLoan(getProduct, res.data)
				    			console.log("res", res)
				    			//this.isLoading = false
							}).catch(err => {
								console.log(err)
							})
			    			
			    		}

		    		}
		    	}
	          	else {
	            	return false;
	          	}
    		})
    	},
    	calculateLoan(getProduct, latestLoan){
    		console.log("getProduct", getProduct)
    		console.log("latestLoan", latestLoan)
    		let evalForm = cloneDeep(this.evaluationForm)
    		//To calculate redemption insurance (redemp * year)
    		let redemptionInsurance = parseFloat(this.dataDefaultSettings.loan_redemption_insurance) * (parseInt(evalForm.term)/12)
    		console.log(redemptionInsurance)

    		//Set Amount
    		this.evaluationForm.debit_loan = parseFloat(latestLoan.principal_balance).toFixed(2)
    		this.evaluationForm.credit_loan = parseFloat(latestLoan.principal_balance).toFixed(2)

    		let p_interest = getProduct.prepaid_interest
			let prepaid_interest = 0
			let service_charge = 0
			let saving_retention = 0
			let redemption_insurance = 0

			if(p_interest){
				 prepaid_interest = amount * (Number(p_interest) / 100)
				console.log("prepaid_interest", prepaid_interest)
			}

			//Calculate Service Charge
			if(evalForm.service_charge){

    			let getServiceFee = getProduct.serviceCharge.find(sc => { return Number(sc.month_term) == Number(duration) && amount < Number(sc.max_amount) && amount > Number(sc.min_amount)})
    			if(getServiceFee){
    				service_charge = amount * (Number(getServiceFee.percentage) / 100)
    				console.log("service_charge", service_charge)
    			}

    		}

    		/*//Calculate Retention
			if(evalForm.saving_retention){

    			saving_retention = amount * (Number(this.dataDefaultSettings.loan_refundable_retention) / 100)
    			console.log("saving_retention", saving_retention)
    			
    		}*/

    		//Calculate Insurance
			if(getProduct.redemption_insurance){

    			redemption_insurance = amount * (Number(redemptionInsurance) / 100)
    			console.log("redemption_insurance", redemption_insurance)
    			
    		}

    		//Quincena
    		let principal_amort = ( amount / duration ) / 2

    		principal_amort = principal_amort.toFixed(2)
    		console.log("principal_amort", principal_amort)

    		let prepaid_amort = 0;

    		if(getProduct.id == 2){
    			let pre_amort = 0
    			let preinterest = this.p_interest
    			let preMonth = 0
    			let preRemainingMonth = 0

    			if(Number(duration) == 12){
    				preMonth = 5
    				preRemainingMonth = 7
    			}

    			else if(Number(duration) == 18){
    				preMonth = 7.5
    				preRemainingMonth = 11.5
    			}

    			else if(Number(duration) == 24){
    				preMonth = 10
    				preRemainingMonth = 14
    			}

    			else if(Number(duration) == 36){
    				preMonth = 15
    				preRemainingMonth = 21
    			}

    			pre_amort = (amount * this.$nf.numberFixed(preinterest, 2)) / preMonth
				pre_amort = pre_amort * preRemainingMonth
				pre_amort = pre_amort / (duration * 2)
				prepaid_amort = pre_amort.toFixed2
    		}


			this.evaluationForm.prepaid_amortization_quincena = prepaid_interest/2
			this.evaluationForm.service_charge_amount = service_charge
			//this.evaluationForm.saving_retention_amount = saving_retention
			this.evaluationForm.redemption_insurance_amount = redemption_insurance

			this.evaluationForm.principal_amortization_quincena = principal_amort
			this.evaluationForm.prepaid_amortization_quincena = prepaid_amort
    	},
    	newLoan(){
    		if(this.memberDetails.id != null){
    			this.disabledBox = false
    			this.$refs.product_loan_id.focus() 
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