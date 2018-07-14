<template>
	<div class = "loan-evaluation">
		<div class="box box-info">
            <div class="box-header with-border">
              	<h3 class="box-title">Loan Evaluation</h3>
            </div>
	        <div class = "box-body">
	        	<el-row :gutter = "20">
	        		<el-col :span="12">
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

			        	<el-form label-position="right" label-width="80px" :model="evaluationForm" :rules = "formRule">
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
								</el-col>
				        		<el-col :span="18">
								  	<el-form-item label="Duration" prop = "duration">
								  		<el-input-number v-model="evaluationForm.duration" :min = "1" :controls = "false" @keyup.enter.native = "selectLoanDuration" ref = "duration"></el-input-number>
								  	</el-form-item>
								</el-col>
								<el-col :span="6">
									<el-input v-model="evaluationForm.duration_type"  :disabled = "true"></el-input>
								</el-col>
							</el-row>
							<hr>
				        	<h3>Existing Loan</h3>
							<el-row :gutter = "20">
				        		<el-col :span="24">
								  	<el-form-item label="Transaction Number" label-width = "180px">
								    	<el-input v-model="evaluationForm.transacion_number" :disabled = "true"></el-input>
								  	</el-form-item>
				        		</el-col>
				        		<el-col :span="24">
								  	<el-form-item label="Principal Loan" label-width = "180px">
								    	<el-input v-model="evaluationForm.transacion_number" :disabled = "true"></el-input>
								  	</el-form-item>
				        		</el-col>
				        		<el-col :span="24">
								  	<el-form-item label="Date" label-width = "180px">
								    	<el-input v-model="evaluationForm.transacion_number" :disabled = "true"></el-input>
								  	</el-form-item>
				        		</el-col>
				        		<el-col :span="24">
								  	<el-form-item label="Balance" label-width = "180px">
								    	<el-input v-model="evaluationForm.transacion_number" :disabled = "true"></el-input>
								  	</el-form-item>
				        		</el-col>
				        		<el-col :span="18">
								  	<el-form-item label="Loan Amount" label-width = "180px" prop = "loan_amount">
								    	<el-input v-model="evaluationForm.loan_amount" ref = loan_amount></el-input>
								  	</el-form-item>
				        		</el-col>
				        		<el-col :span="4">
								    <el-button type = "primary" @click="evaluateLoan">Evaluate</el-button>
				        		</el-col>
				        	</el-row>
				        	<hr>
							<el-row :gutter = "20" class = "debit-credit-content">
								<el-col :span="12">
									<h3>Debit</h3>
								</el-col>
								<el-col :span="12">
									<h3>Credit</h3>
								</el-col>
								<el-col :span="8">
									<el-form-item label="" label-width = "0">
								    	<el-input v-model="evaluationForm.transacion_number" :disabled = "true"></el-input>
								  	</el-form-item>
								</el-col>
								<el-col :span="8">
									<p class = "loan-label">LOAN</p>
								</el-col>
								<el-col :span="8">
									<el-form-item label="" label-width = "0">
								    	<el-input v-model="evaluationForm.transacion_number" :disabled = "true"></el-input>
								  	</el-form-item>
								</el-col>
							</el-row>
			        	</el-form>
					</el-col>
	        		<el-col :span="12">
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
	props: ['dataLoanProduct'],
	data: function () {
		let form = {product_loan_id : null, duration : null, duration_type : "Months", transacion_number : null, principal_loan : null, loan_date : null, loan_balance : null, loan_amount : null}
		return {
			evaluationForm 			: form,
			loanProduct 			: this.dataLoanProduct,
			memberDetails			: {id : null, fullname : null, station : {}},
			showSearchModal			: true,
			accountLoanList 		: [],
			loanDuration 			: null,
			formRule 				: [],
			minTerm 				: 1,
			maxTerm					: 12
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

		this.formRule = {
  			product_loan_id : [{ required: true, message: 'Product cannot be blank.', trigger: 'change' },],
  			duration : [{ required: true, message: 'Term duration cannot be blank.', trigger: 'blur' },
  				{ validator: validateDuration, trigger: 'blur', trigger: 'blur' },],
  			loan_amount : [{ required: true, message: 'Amount cannot be blank.', trigger: 'blur' },],
		}
	},
	methods:{		
    	populateField(data){
    		this.memberDetails = data
    		//this.getOtherAccounts(data.id)
    		this.$refs.product_loan_id.focus()
    	},
    	selectLoanProduct(val){
    		console.log("Here", val)
    		this.$refs.duration.focus()

    	},
    	selectLoanDuration(){
    		this.$refs.loan_amount.focus()
    	},
    	getAccounLoanInfo(member_id){

    		this.$API.getAccounLoanInfo(member_id)
    		.then(res => {

			}).catch(err => {
				console.log(err)
			})
    	},
    	evaluateLoan(){    		
    		let vm = this	
    		this.$refs.loanEvaluateForm.validate((valid) => {
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
	        })
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
  		

	}
</style>