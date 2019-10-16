<template>
	<div class="loan-evaluation-form" v-loading = "pageLoading">
		<div id = "printPageContent">
            <div>
            	<print-header></print-header>
            </div>
            <div class = "row">
	        	<div class = "col-md-12">
	        		<h4>Loan Evaluation</h4>
	        		<table style="text-align: left;">
	        			<tr>
	        				<th>Name: </th>
	        				<td>{{ memberDetails.fullname }}</td>
	        				<th>Station: </th>
	        				<td>
	        					<span v-if = "memberDetails.station">{{ memberDetails.station.name }}</span>
	        				</td>
	        			</tr>
	        			<tr>
	        				<th>Type of Loan: </th>
	        				<td>
	        					<span v-if = "loanProduct">{{ loanProduct.product_name }}</span>
	        				</td>
	        				<th>Date: </th>
	        				<td> {{ $systemDate }} </td>
	        			</tr>
	        			<tr>
	        				<th>Share Capital: </th>
	        				<td>
	        					<span v-if = "shareAccount">{{ $nf.formatNumber(shareAccount.balance) }}</span>
	        				</td>
	        			</tr>
	        			<tr>
	        				<th>Maximum Loan Amount: </th>
	        				<td>
	        					<span v-if = "shareAccount">{{ $nf.formatNumber(shareAccount.max_amount) }}</span>
	        				</td>
	        			</tr>
	        		</table>
	        	</div>
	        </div>
	        <div class = "row">
	        	<div class = "col-md-8" style = "margin-top: 20px;">
	        		<table style="min-width: 400px;">
						<tr>
		        			<th>
		        				<h3>Debit</h3>
		        			</th>
		        			<td>
		        			</td>
		        			<th>
		        				<h3>Credit</h3>
		        			</th>
		        		</tr>

		        		<tr>
		        			<td>
		        				<span>{{ $nf.formatNumber(evaluationForm.debit_loan) }}</span>
						  	</td>
						  	<th>
		        				<p>LOAN</p>
						  	</th>
						  	<td>
						  		<span>{{ $nf.formatNumber(evaluationForm.credit_loan) }}</span>
						  	</td>
						</tr>

						<tr>
						  	<td>
						  		<span>{{ $nf.formatNumber(evaluationForm.debit_interest) }}</span>
						  	</td>
						  	<th>
		        				<p>Interest</p>
						  	</th>
						  	<td>
						  		<span>{{ $nf.formatNumber(evaluationForm.credit_interest) }}</span>
						  	</td>
						</tr>

						<tr>
							<td>
						  		<span>{{ $nf.formatNumber(evaluationForm.debit_preinterest) }}</span>
						  	</td>
						  	<th>
		        				<p>Prepaid Int</p>
						  	</th>
						  	<td>
						  		<span>{{ $nf.formatNumber(evaluationForm.credit_preinterest) }}</span>
						  	</td>
						</tr>

						<tr>
							<td>
						  		<span>{{ $nf.formatNumber(evaluationForm.debit_redemption_ins) }}</span>
						  	</td>
						  	<th>
		        				<p>Redemp. Ins.</p>
						  	</th>
						  	<td>
						  		<span>{{ $nf.formatNumber(evaluationForm.credit_redemption_ins) }}</span>
						  	</td>
						</tr>
						
						<tr>
						  	<td>
						  		<p>&nbsp;</p>
						  	</td>
						  	<th>
		        				<p>Service Charge</p>
						  	</th>
						  	<td>
						  		<span>{{ $nf.formatNumber(evaluationForm.service_charge_amount) }}</span>
						  	</td>
						</tr>

						<tr>
						  	<td>
						  		<p>&nbsp;</p>
						  	</td>
						  	<th>
		        				<p>Savings(1%)</p>
						  	</th>
						  	<td>
						  		<span>{{ $nf.formatNumber(evaluationForm.savings_retention) }}</span>
						  	</td>
						</tr>


						<tr>
						  	<td>
						  		<p>&nbsp;</p>
						  	</td>
						  	<th>
		        				<p>Notary</p>
						  	</th>
						  	<td>
						  		<span>{{ $nf.formatNumber(evaluationForm.notary_amount) }}</span>
						  	</td>
						</tr>

						<tr>
						  	<td>
						  		<p>&nbsp;</p>
						  	</td>
						  	<th>
		        				<p><b>NET CASH</b></p>
						  	</th>
						  	<td>
						  		<span>{{ $nf.formatNumber(evaluationForm.net_cash) }}</span>
						  	</td>
						</tr>
						<tr>
							<td colspan="3" style="border-top: 2px solid #000;"></td>
						</tr>
						<tr>
						  	<td>
						  		<span>{{ $nf.formatNumber(evaluationForm.debit_total) }}</span>
						  	</td>
						  	<th>
		        				<p>TOTAL</p>
						  	</th>
						  	<td>
						  		<span>{{ $nf.formatNumber(evaluationForm.credit_total) }}</span>
						  	</td>
						</tr>
					</table>
	        	</div>
	        </div>
	        <div class = "row">
	        	<div class = "col-md-12" style = "margin-top: 20px;">
	        		<table style="text-align: left;">
	        			<tr>
	        				<th>Loan Amount: </th>
	        				<td>
	        					<span>{{ $nf.formatNumber(evaluationForm.amount) }}</span>
	        				</td>

	        				<th>Previous Loan Amount: </th>
	        				<td>
	        					<span v-if = "latestLoan">{{ $nf.formatNumber(latestLoan.principal) }}</span>
	        				</td>
	        			</tr>
	        			<tr>
	        				<th>Payment/Quincena: </th>
	        				<td>
	        					<span>{{ $nf.formatNumber(evaluationForm.principal_amortization_quincena) }}</span>
	        				</td>


	        				<th>Previous Loan Balance: </th>
	        				<td>
	        					<span v-if = "latestLoan">{{ $nf.formatNumber(latestLoan.principal_balance) }}</span>
	        				</td>
	        			</tr>
	        			<tr>

	        				<th>PI Payment/Quincena: </th>
	        				<td>
	        					<span>{{ $nf.formatNumber(evaluationForm.prepaid_amortization_quincena) }}</span>
	        				</td>

	        				<th>Date of Last Loan: </th>
	        				<td>
	        					<span v-if = "latestLoan">{{ latestLoan.release_date }}</span>
	        				</td>
	        			</tr>
	        		</table>
	        	</div>
	        </div>
        </div>
        <div>
        	<el-button type = "default" @click = "printPage()" ref = "print">Print</el-button> 
        </div>
	</div>
</template>
<script>

window.noty = require('noty')
import Noty from 'noty'
import cloneDeep from 'lodash/cloneDeep'  

import VoucherForm from '../Voucher/VoucherForm.vue' 

import fileExport from '../../mixins/fileExport'
import swalAlert from '../../mixins/swalAlert.js'

import VueHtmlToPaper from 'vue-html-to-paper';
Vue.use(VueHtmlToPaper);

export default { 
	props: { 
		/*
		Should contain: evaluationForm, memberDetails, shareDetails, latestLoan, loanProduct, otherLoan
		*/
		pageData : {
			type: Object,
			required: true
		},
		toPrint : {
			type : Boolean, 
			default : false
		}
	},
	data: function () {
		let shareAccount = {}
		if(this.pageData.shareDetails){
			shareAccount = this.pageData.shareDetails
			shareAccount['max_amount'] = Number(this.pageData.shareDetails.balance) * 4
		}

		return{
			evaluationForm 			: this.pageData.evaluationForm,
			memberDetails 			: this.pageData.memberDetails,
			shareAccount 			: shareAccount,
			latestLoan 				: this.pageData.latestLoan,
			loanProduct 			: this.pageData.loanProduct,
			pageLoading 			: false,
			printOptions 			: {
			 	name: '_blank',
			  	specs: [
			    	'fullscreen=yes',
			    	'titlebar=yes',
			    	'scrollbars=yes'
			  	],
			  	styles: [
			    	'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css',
			  	]
			},
		}
	},
	created(){
		
	},
	mounted(){
		if(this.toPrint){
			setTimeout(() => {
                this.printPage()
            }, 500);
		}
	},
	methods: {
		printPage(){
			this.$htmlToPaper('printPageContent', () => {
			  	this.$EventDispatcher.fire('CLOSE_LOAN_EVALUATION_FORM')
			});

			
		},
	}
}

</script>
<style lang="scss">
  	@import '../../assets/site.scss';
  	@import '~noty/src/noty.scss';

  	.loan-evaluation-form{
  		table {
  			tr {
  				td{
  					padding: 0px 10px;
  				}
  			}
  			
  		}

		
	}
</style>