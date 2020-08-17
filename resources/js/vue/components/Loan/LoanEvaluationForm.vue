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
		        				<span class = "label-title">LOAN</span>
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
		        				<span class = "label-title">Interest</span>
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
		        				<span class = "label-title">Prepaid Int</span>
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
		        				<span class = "label-title">Redemp. Ins.</span>
						  	</th>
						  	<td>
						  		<span>{{ $nf.formatNumber(evaluationForm.credit_redemption_ins) }}</span>
						  	</td>
						</tr>
						
						<tr>
						  	<td>
						  		<span class = "label-title">&nbsp;</span>
						  	</td>
						  	<th>
		        				<span class = "label-title">Service Charge</span>
						  	</th>
						  	<td>
						  		<span>{{ $nf.formatNumber(evaluationForm.service_charge_amount) }}</span>
						  	</td>
						</tr>

						<tr>
						  	<td>
						  		<span class = "label-title">&nbsp;</span>
						  	</td>
						  	<th>
		        				<span class = "label-title">Retention</span>
						  	</th>
						  	<td>
						  		<span>{{ $nf.formatNumber(evaluationForm.savings_retention) }}</span>
						  	</td>
						</tr>


						<tr>
						  	<td>
						  		<span class = "label-title">&nbsp;</span>
						  	</td>
						  	<th>
		        				<span class = "label-title">Notary</span>
						  	</th>
						  	<td>
						  		<span>{{ $nf.formatNumber(evaluationForm.notary_amount) }}</span>
						  	</td>
						</tr>

						<tr>
						  	<td>
						  		<span class = "label-title">&nbsp;</span>
						  	</td>
						  	<th>
		        				<span class = "label-title"><b>NET CASH</b></span>
						  	</th>
						  	<td>
						  		<span style="font-weight: bold; font-size: 20px !important;">{{ $nf.formatNumber(evaluationForm.net_cash) }}</span>
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
		        				<span class = "label-title">TOTAL</span>
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
	        				<td>
	        					&nbsp;&nbsp;&nbsp;&nbsp;
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
	        				<td>
	        					&nbsp;&nbsp;&nbsp;&nbsp;
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
	        				<td>
	        					&nbsp;&nbsp;&nbsp;&nbsp;
	        				</td>
	        				<th>Date of Last Loan: </th>
	        				<td>
	        					<span v-if = "latestLoan">{{ latestLoan.release_date }}</span>
	        				</td>
	        			</tr>
	        			<tr>
	        				<th>Term: </th>
	        				<td>
	        					<span>{{ $nf.formatNumber(evaluationForm.duration) }}</span>
	        				</td>
	        			</tr>
	        		</table>
	        	</div>
	        	<div class = "col-md-12" style = "margin-top: 20px;">
	        		<h3>OTHER LOANS:</h3>
	        		<table class = "table table-bordered" style="text-align: left;">
	        			<thead>
	        				<tr>
		        				<th>Loan Type </th>
		        				<th>Total Arrears</th>
		        				<th>Loan Amount</th>
		        				<th>Balance</th>
		        				<th>Date of Loan</th>
		        			</tr>
	        			</thead>
	        			<tbody>
	        				<tr v-for = "loan in otherLoans">
	        					<td>{{ loan.product.product_name}}</td>
	        					<td>{{ $nf.formatNumber(loan.arrears, 2) }}</td>
	        					<td>{{ $nf.formatNumber(loan.principal, 2) }}</td>
	        					<td>{{ $nf.formatNumber(loan.principal_balance, 2) }}</td>
	        					<td>{{ loan.release_date }}</td>
	        				</tr>
	        			</tbody>
	        			
	        		</table>
	        	</div>

	        	<div class = "col-md-12" style = "margin-top: 20px;">
	        		<h3>Payments:</h3>
	        		<table class = "table table-bordered" style="text-align: left;">
	        			<thead>
	        				<tr>
		        				<th>DateType </th>
		        				<th>GV / OR Num</th>
		        				<th>Amount Paid</th>
		        				<th>Balance</th>
		        				<th>Remarks</th>
		        			</tr>
	        			</thead>
	        			<tbody>
	        				<tr v-for = "trans in loanTransaction">
	        					<td>{{ trans.date_posted}}</td>
	        					<td>{{ trans.OR_no }}</td>
	        					<td>{{ $nf.formatNumber(trans.principal_paid, 2) }}</td>
	        					<td>{{ $nf.formatNumber(trans.running_balance, 2) }}</td>
	        					<td>{{ trans.remarks }}</td>
	        				</tr>
	        			</tbody>
	        			
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
var printOptions =  {
 	name: '_blank',
  	specs: [
    	'fullscreen=yes',
    	'titlebar=yes',
    	'scrollbars=yes'
  	],
  	styles: [
    	//"https://unpkg.com/kidlat-css/css/kidlat.css",
    	window.Yii.baseUrl + "/css/bootstrap.min.css",
    	window.Yii.baseUrl + "/css/mpdf.css"
  	]
}
console.log('printOptions', printOptions)
Vue.use(VueHtmlToPaper, printOptions);

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
			otherLoans 				: this.pageData.otherLoans,
			loanTransaction 		: this.pageData.loanTransaction,
			pageLoading 			: false,
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
	@import '../../assets/mpdf.scss'; 
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