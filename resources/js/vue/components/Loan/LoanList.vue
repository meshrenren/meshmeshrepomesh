<template>
	<div class = "loan-list">
		<div class="box box-info" v-loading = "pageLoading">
            <div class="box-header with-border">
              	<h3 class="box-title">View Loans</h3>
            </div>
	        <div class = "box-body">
	        	<el-row :gutter = "20">
	        		<el-col :span="10">
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
							<div class = "member-loan">
								<div>
			        				<h4>Member's List of Loan</h4>
			        			</div>
			        			<div class = "toolbar-right">
			        				<el-button size="mini" @click="printLoanSummary('print')">Print Summary</el-button>
			        				<el-button size="mini" @click="printRebates('print')">Rebates</el-button>
			        			</div>
								<el-table class = "mt-20" :data="accountLoanList" height = "350px" stripe border>
						            <el-table-column label="Action">
						                <template slot-scope="scope">
						                    <!-- <el-button size="mini" @click="selectAccount(scope.index, scope.row)">Transaction</el-button><br> -->
						                    <el-button class = "mt-5" size="mini" @click="getLoanHistory(scope.index, scope.row)">View</el-button>
						                </template>
						            </el-table-column>
						            <el-table-column label="Date">
						                <template slot-scope="scope">
						                    <span>{{ scope.row.release_date }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Type" width = "200px">
						                <template slot-scope="scope">
						                    <span>{{ scope.row.product.product_name }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Principal">
						                <template slot-scope="scope">
						                    <span>{{ $nf.formatNumber(scope.row.principal, 2) }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Balance">
						                <template slot-scope="scope">
						                    <span>{{ $nf.formatNumber(scope.row.principal_balance, 2)  }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Per Quincena">
						                <template slot-scope="scope">
						                    <span>{{ $nf.formatNumber(scope.row.principal_amortization_quincena, 2)  }}</span>  
						                    <span v-if = "scope.row.prepaid_amortization_quincena && scope.row.prepaid_amortization_quincena > 0"> / <br> {{ $nf.formatNumber(scope.row.prepaid_amortization_quincena, 2)  }}</span> 
						                </template>
						            </el-table-column>
						            <el-table-column label="Arrears">
						                <template slot-scope="scope">
						                    <span v-if = "scope.row.arrears && scope.row.arrears > 0">{{ $nf.formatNumber(scope.row.arrears, 2)  }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Interest">
						                <template slot-scope="scope">
						                    <span v-if = "scope.row.arrears && scope.row.arrears > 0">{{ $nf.formatNumber(scope.row.interest_earned, 2)  }}</span>
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
			       	<el-col :span = "14">
			       		<div class = "loan-ledger">
		        			<!-- <h4>Transactions</h4> -->
		        			<label>Loan Account : </label> 
		        			<span v-if = "selectedAccount && selectedAccount.product"> {{ selectedAccount.product.product_name}}</span>
		        			<!-- <div class = "toolbar-right">
		        				<el-button class = "mt-5" size="mini" @click="printLedger(scope.index, scope.row)">Print Ledger</el-button>
		        			</div> -->
		        			<div class = "toolbar-right">
		        				<el-button size="mini" @click="printLoanLedger('print')">Print Ledger</el-button>
		        			</div>

							<el-table class = "mt-20" 
								:data="getAllHistory"
								height = "450px" 
								stripe border>

					            <el-table-column label="Date">
					                <template slot-scope="scope">
					                    <span>{{ $df.formatDate(scope.row.transaction_date, 'YYYY-MM-DD') }}</span>
					                </template>
					            </el-table-column>
					            <el-table-column label="Reference No">
					                <template slot-scope="scope">
					                    <span>{{ scope.row.OR_no }}</span>
					                </template>
					            </el-table-column>
					            <el-table-column label="Principal">
					                <template slot-scope="scope">
					                    <span v-if = "scope.row.transaction_type == 'RELEASE'">
					                    	{{ $nf.formatNumber(scope.row.amount) }}
					                    </span>
					                </template>
					            </el-table-column>
					            <el-table-column label="Amount Paid">
					                <template slot-scope="scope">
					                    <span>{{ $nf.formatNumber(scope.row.principal_paid) }}</span>
					                </template>
					            </el-table-column>
					            <el-table-column label="Running Balance">
					                <template slot-scope="scope">
					                    <span>{{ $nf.formatNumber(scope.row.running_balance) }}</span>
					                </template>
					            </el-table-column>
					            <el-table-column label="Prepaid Interest Paid">
					                <template slot-scope="scope">
					                    <span>{{ $nf.formatNumber(scope.row.prepaid_intpaid) }}</span>
					                </template>
					            </el-table-column>
					            <el-table-column label="Interest">
					                <template slot-scope="scope">
					                    <span>{{ $nf.formatNumber(scope.row.interest_earned) }}</span>
					                </template>
					            </el-table-column>
					            <el-table-column label="Type">
					                <template slot-scope="scope">
					                    <span>{{ scope.row.transaction_type }}</span>
					                </template>
					            </el-table-column>
		       				</el-table>		       				
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
	window.noty = require('noty')
    import Noty from 'noty'
    import cloneDeep from 'lodash/cloneDeep'    
    import _forEach from 'lodash/forEach' 
    import _concat from 'lodash/concat'

    import fileExport from '../../mixins/fileExport'

export default {
	props: [],
	data: function () {
		return {
			memberDetails			: {id : null, fullname : null, station : {}},
			showSearchModal			: false,
			accountLoanList 		: [],
			pageLoading 			: false,
			loanTransaction 		: [],
			selectedAccount 		: {},
			allLoanAccount 			: []
		}
	},
	mixins: [fileExport],
	created(){
	},
	computed:{
		getAllHistory(){
			let allAccount = this.allLoanAccount
			let transaction = []
			_forEach(allAccount, acc =>{
				if(acc.loanTransaction && acc.loanTransaction.length > 0){
					let findReleaseIndex = acc.loanTransaction.findIndex(fn => fn.transaction_type == 'RELEASE')
					if(findReleaseIndex >= 0){
						let accLoan = cloneDeep(acc)
						accLoan.product = null
						accLoan.loanTransaction = null
						acc.loanTransaction[findReleaseIndex]['loan_account'] = accLoan
					}

					transaction = _concat(transaction, acc.loanTransaction)
				}
			})

			return transaction;
		}
	},
	methods:{	
		printLoanSummary(type){
            if(this.accountLoanList.length == 0){
                new Noty({
                    theme: 'relax',
                    type: "error",
                    layout: 'topRight',
                    text: "No loan to export.",
                    timeout: 3000
                }).show();
                return
            }

            let dataLoan = {}
            let dataAccount = {}
            dataAccount['fullname'] = this.memberDetails.last_name + " "  + this.memberDetails.first_name + " " + this.memberDetails.middle_name
            dataAccount['station'] = ""
            if(this.memberDetails.station){
                dataAccount['station']  = this.memberDetails.station.name
            }
            let totalPrincipal = 0
            let totalBalance = 0
            _forEach(cloneDeep(this.accountLoanList), rs=>{
            	if(parseFloat(rs.principal_balance) > 0){
	                totalPrincipal = parseFloat(totalPrincipal) + parseFloat(rs.principal)
	                totalBalance = parseFloat(totalBalance) + parseFloat(rs.principal_balance)
            	}
            })
            dataAccount['totalPrincipal'] = totalPrincipal
            dataAccount['totalBalance'] = totalBalance

            dataLoan['details'] = dataAccount
            dataLoan['loanList'] = this.accountLoanList
            dataLoan['member'] = this.memberDetails

            this.pageLoading = true
            this.$API.Loan.printSummary(dataLoan, 'print')
            .then(result => {
                let res = result.data
                if(type == 'pdf'){
                    this.exporter(type, 'Loan Summary', res)
                }
                else if(type == 'print'){
                    this.winPrint(res.data, 'Loan Summary')
                }
            })
            .catch(err => { console.log(err)})
            .then(_ => { this.pageLoading = false })
        },
        printRebates(type){
        	let dataLoan = {}
            let dataAccount = {}
            dataAccount['fullname'] = this.memberDetails.last_name + " "  + this.memberDetails.first_name + " " + this.memberDetails.middle_name
            dataAccount['station'] = ""
            dataAccount['id'] = this.memberDetails.id
            if(this.memberDetails.station){
                dataAccount['station']  = this.memberDetails.station.name
            }
        	dataLoan['details'] = dataAccount
            dataLoan['member'] = this.memberDetails

            this.pageLoading = true
        	this.$API.Loan.printRebates(dataLoan, 'print')
            .then(result => {
                let res = result.data
                if(type == 'pdf'){
                    this.exporter(type, 'Loan Rebates', res)
                }
                else if(type == 'print'){
                    this.winPrint(res.data, 'Loan Rebates')
                }
            })
            .catch(err => { console.log(err)})
            .then(_ => { this.pageLoading = false })
        },
        printLoanLedger(type){
        	if(this.getAllHistory.length == 0){
                new Noty({
                    theme: 'relax',
                    type: "error",
                    layout: 'topRight',
                    text: "No ledger to export.",
                    timeout: 3000
                }).show();
                return
            }

            let dataLoan = {}

            let dataAccount = {}
            dataAccount['fullname'] = this.memberDetails.last_name + " "  + this.memberDetails.first_name + " " + this.memberDetails.middle_name
            dataAccount['loan_type'] = this.selectedAccount && this.selectedAccount.product ? this.selectedAccount.product.name : ""

            dataLoan['details'] = dataAccount
            dataLoan['loanledger'] = this.getAllHistory
            dataLoan['member'] = this.memberDetails
            console.log('dataAccount', dataAccount)


            this.$API.Loan.printLedger(dataLoan, 'print')
            .then(result => {
                let res = result.data
                if(type == 'pdf'){
                    this.exporter(type, 'Loan Ledger', res)
                }
                else if(type == 'print'){
                    this.winPrint(res.data, 'Loan Ledger')
                }
            })
            .catch(err => { console.log(err)})
            .then(_ => { this.pageLoading = false })
        },
    	populateField(data){
    		this.memberDetails = data
    		this.memberDetails.share_capital = null
    		if(data.shareaccount != null){
    			this.memberDetails.share_capital = data.shareaccount.balance
    		}
    		this.getAccounLoanInfo(this.memberDetails.id)
    		/*console.log(this.$refs.newLoan)
    		this.$refs.newLoan.focus()*/
    	},

    	getAccounLoanInfo(member_id){
    		console.log()
    		this.pageLoading = true
    		this.allLoanAccount = []

            this.$API.Loan.getAccounLoanInfo(member_id)
            .then(result => {
                let res = result.data
                this.accountLoanList = res
            })
            .catch(err => {
                console.log(err)
            })
            .then(_ => { 
                this.pageLoading = false
            })
    	},

    	selectAccount(index, data){
    		this.pageLoading = true
    		this.selectedAccount = data

            this.$API.Loan.getLoanTransaction(data.account_no)
            .then(result => {
                let res = result.data
                this.loanTransaction = res
            })
            .catch(err => {
                console.log(err)
            })
            .then(_ => { 
                this.pageLoading = false
            })
    	},

    	getLoanHistory(index, data){
    		this.pageLoading = true
    		this.selectedAccount = data

            this.$API.Loan.getLoanHistory(this.memberDetails.id, data.loan_id)
            .then(result => {
                let res = result.data
                console.log("res", res)
                this.allLoanAccount = res
            })
            .catch(err => {
                console.log(err)
            })
            .then(_ => { 
                this.pageLoading = false
            })
    	}
    }
}

</script>
<style lang="scss">
	.loan-list{
  		.box{
  			h3{
	  			margin-top: 5px;
	  		}
	  		.el-form-item{
			    margin-bottom: 0px !important;
			}
		}
		.member-loan{
			position: relative;
		}

		.loan-ledger{
			position: relative;
		}
	}
</style>