<template>
	<div class = "loan-evaluation">
		<div class="box box-info">
            <div class="box-header with-border">
              	<h3 class="box-title">Loan Evaluation</h3>
            </div>
	        <div class = "box-body">
	        	<el-row :gutter = "20">
	        		<el-col :span="12">
	        			<div class = "box-content">
				        	<el-form label-position="right" label-width="80px" :model="memberDetails" ref = "loanEvaluateForm">
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
								</el-row>
							</el-form>
							<hr>
							<div class = "Loan List">
			        			<h4>Member's List of Loan</h4>
								<el-table :data="accountLoanList" style="width: 100%" stripe border>
						            <el-table-column label="Date Transaction">
						                <template slot-scope="scope">
						                    <span style="margin-left: 10px">{{ scope.row.date }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Principal Loan">
						                <template slot-scope="scope">
						                    <span style="margin-left: 10px">{{ scope.row.principal_loan }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Balance">
						                <template slot-scope="scope">
						                    <span style="margin-left: 10px">{{ scope.row.term }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Loan Type">
						                <template slot-scope="scope">
						                    <span style="margin-left: 10px">{{ scope.row.maturity_date }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Duration">
						                <template slot-scope="scope">
						                    <span style="margin-left: 10px">{{ scope.row.maturity_date }}</span>
						                </template>
						            </el-table-column>
			       				</el-table>
			       				<div style = "margin-top: 10px;">
			       					<el-button type = "primary" @click = "newLoan()" ref = "newLoan">New Loan</el-button> 
			       				</div>
			       			</div>
		       			</div>
					</el-col>
	        		<el-col :span="12">
	        			<div class = "box-content" :class = "disabledBox ? 'disabled-box' : ''">
				        	<el-form label-position="right" label-width="200px" :model="evaluationForm" :rules = "formRule" ref = "evaluationForm">
				        		<el-row :gutter = "20">
					        		<el-col :span="24">
									  	<el-form-item label="Type" prop = "product_loan_id">
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
											  	<el-form-item label="Duration" prop = "duration">
											  		<el-input-number v-model="evaluationForm.duration" :min = "1" :controls = "false" @keyup.enter.native = "selectLoanDuration" ref = "duration" :disabled = "disabledBox"></el-input-number>
											  	</el-form-item>
											</el-col>
									  		<el-col :span="6">
									  			<el-input v-model="evaluationForm.duration_type"  :disabled = "true"></el-input>
									  		</el-col>
										</el-row>
									  	<el-form-item label="Loan Amount" prop = "amount">
									    	<el-input v-model="evaluationForm.amount" @keyup.enter.native = "enterLoanAmount" ref = "amount" :disabled = "disabledBox"></el-input>
									  	</el-form-item>
					        			<h3>Deductions</h3>
					        			<el-form-item label="Loan Repayment">
									    	<el-input v-model="evaluationForm.loan_repayment" :disabled = "true"></el-input>
									  	</el-form-item>
					        			<el-form-item label="Service Change">
							        		<el-row :gutter = "10">
								        		<el-col :span="2">
									    			<el-checkbox v-model="evaluationForm.service_charge"></el-checkbox>
									    		</el-col>
								        		<el-col :span="21">
									    			<el-input v-model="evaluationForm.service_charge_amount" :disabled = "true"></el-input>
									    		</el-col>
									    	</el-row>
									  	</el-form-item> 
									  	
					        			<el-form-item label="Saving Retention">
							        		<el-row :gutter = "10">
								        		<el-col :span="2">
									    			<el-checkbox v-model="evaluationForm.saving_retention"></el-checkbox>
									    		</el-col>
								        		<el-col :span="21">
									    			<el-input v-model="evaluationForm.saving_retention_amount" :disabled = "true"></el-input>
									    		</el-col>
									    	</el-row>
									  	</el-form-item>
					        			<el-form-item label="Redemption Insurance">
							        		<el-row :gutter = "10">
								        		<el-col :span="2">
									    			<el-checkbox v-model="evaluationForm.redemption_insurance"></el-checkbox>
									    		</el-col>
								        		<el-col :span="21">
									    			<el-input v-model="evaluationForm.redemption_insurance_amount" :disabled = "true"></el-input>
									    		</el-col>
									    	</el-row>
									  	</el-form-item>
					        			<el-form-item label="Prepaid Interest">
									    	<el-input v-model="evaluationForm.prepaid_interest" :disabled = "true"></el-input>
									  	</el-form-item>
									  	<el-form-item label="Loan Amount" prop = "loan_amount">
									    	<el-input v-model="evaluationForm.loan_amount" ref = loan_amount :disabled = "true"></el-input>
									  	</el-form-item>
									  	<el-form-item label="Net Cash" prop = "net_cash">
									    	<el-input v-model="evaluationForm.net_cash" ref = net_cash :disabled = "true"></el-input>
									  	</el-form-item>
									  	<el-form-item label="Principal Amortization / quincena" prop = "principal_amortization_quincena">
									    	<el-input v-model="evaluationForm.principal_amortization_quincena" ref = principal_amortization_quincena :disabled = "true"></el-input>
									  	</el-form-item>
									  	<el-form-item label="Prepaid Amortization / quincena" prop = "prepaid_amortization_quincena">
									    	<el-input v-model="evaluationForm.prepaid_amortization_quincena" ref = prepaid_amortization_quincena :disabled = "true"></el-input>
									  	</el-form-item>
									</el-col>
								</el-row>
				        	</el-form>
				        </div>	        			
	        		</el-col>
				</el-row>
			</div>
		</div>
        <search-member v-if = "showSearchModal" @select="populateField" @close = "showSearchModal = false" >
	  	</search-member>
	</div>
</template>

<script>
	window.noty = require('noty');
    import axios from 'axios'
    import Noty from 'noty'
    import swal from 'sweetalert2/dist/sweetalert2.all.min.js'
    import cloneDeep from 'lodash/cloneDeep'

export default {
	props: ['dataLoanProduct', 'dataDefaultSettings'],
	data: function () {
		let form = {product_loan_id : null, duration : null, duration_type : "Months", amount : null, loan_balance : null, loan_amount : null, loan_repayment : null, service_charge : null, service_charge_amount : null, saving_retention : null, saving_retention_amount : null,  redemption_insurance : null,  redemption_insurance_amount : null, net_cash : null, principal_amortization_quincena : null, prepaid_amortization_quincena : null}
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
			disabledBox 			: true
		}
	},
	created(){
		this.evaluationForm.product_loan_id = this.loanProduct[0].id
		var validateDuration = (rule, value, callback) => {

    		let getProduct = this.loanProduct.find(pr =>{
    			return Number(pr.id) == Number(this.evaluationForm.product_loan_id)
    		})
    		console.log("getProduct", getProduct)
    		if(getProduct){
    			if(Number(value) < Number(getProduct.term_min)){
    				callback(new Error("Term is below minimum allowed loan term!" +getProduct.product_name + " is " +getProduct.term_min + " to " +getProduct.term_max + " months."));
    			}
    			else if(Number(value) > Number(getProduct.term_max)){
    				callback(new Error("Term is above maximum allowed loan term!" +getProduct.product_name + " is " +getProduct.term_min + " to " +getProduct.term_max + " months."));
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
    		//this.getOtherAccounts(data.id)
    		//this.$refs.newLoan.focus()
    	},
    	selectLoanProduct(val){
    		console.log("Here", val)
    		this.$refs.duration.focus()

    	},
    	selectLoanDuration(){
    		this.$refs.amount.focus()
    	},
    	getAccounLoanInfo(member_id){

    		this.$API.getAccounLoanInfo(member_id)
    		.then(res => {

			}).catch(err => {
				console.log(err)
			})
    	},
    	getProduct(product_id){
    		return this.loanProduct.find(lp => { return Number(lp.id) == Number(product_id) })
    	},
    	resetEvaluationForm(){

    	},
    	enterLoanAmount(){
    		console.log("Eva")
    		this.$refs.evaluationForm.validateField('amount', valid => {
    			console.log(valid)
    		})
    	},
    	evaluateLoan(){    		
    		let vm = this	
    		let retention = this.dataDefaultSettings.loan_refundable_retention
    		let insurance = this.dataDefaultSettings.loan_redemption_insurance
    		if(this.evaluationForm.product_loan_id && this.evaluationForm.duration && this.evaluationForm.amount){
    			console.log("Calculate")
    			let getProduct = this.loanProduct.find(lp => { return Number(lp.id) == Number(evaluationForm.product_loan_id) })
    			let amount = Number(evaluationForm.amount)
    			let duration = evaluationForm.duration

    			if(getProduct){
	    			let p_interest = getProduct.prepaid_interest

	    			if(p_interest){
	    				this.evaluationForm.prepaid_interest = amount * (Number(p_interest) / 100)
	    			}

	    			//Calculate Service Charge
	    			if(this.evaluationForm.service_charge){

		    			let getServiceFee = getProduct.serviceCharge.find(sc => { return Number(sc.month_term) == Number(duration) && amount < Number(sc.max_amount) && amount > Number(sc.min_amount)})
		    			if(getServiceFee){
		    				this.evaluationForm.service_charge_amount = amount * (Number(getServiceFee.percentage) / 100)
		    			}

		    		}

		    		//Calculate Retention
	    			if(this.evaluationForm.saving_retention){

		    			this.evaluationForm.saving_retention_amount = amount * (Number(this.dataDefaultSettings.loan_refundable_retention) / 100)
		    			
		    		}

		    		//Calculate Insurance
	    			if(this.evaluationForm.redemption_insurance){

		    			this.evaluationForm.redemption_insurance_amount = amount * (Number(this.dataDefaultSettings.loan_redemption_insurance) / 100)
		    			
		    		}
	    		}

    		}
    		/*this.$refs.loanEvaluateForm.validate((valid) => {
	          	if (valid) {
	          		let data = {
	          			member_id 			: this.memberDetails.id,
	          			product_loan_id 	: this.evaluationForm.product_loan_id,
	          			duration 			: this.evaluationForm.duration,
	          			loan_amount 		: this.evaluationForm.loan_amount
	          		}
	          		this.$API.evaluateLoan(data)
		    		.then(res => {

					}).catch(err => {
						console.log(err)
					})
	          	}
	        })*/
    	},
    	newLoan(){

    		this.disabledBox = false
    		this.$refs.product_loan_id.focus()  		
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