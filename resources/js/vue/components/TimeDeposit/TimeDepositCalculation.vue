<template>
	<div class="time-deposit-calculation" v-loading = "pageLoading">
		<h3>Transactions</h3>
		<el-table
	      	:data="transactionList"
	      	style="width: 100%"
	      	v-loading = "tableLoading">
		    <el-table-column
		        prop="transaction_type"
		        label="Transaction">
		    </el-table-column>
		    <el-table-column
		        prop="amount"
		        label="Amount"
		        width="100px">
		        <template slot-scope="scope">
                    <span>{{ $nf.formatNumber(scope.row.amount) }}</span>
                </template>
		    </el-table-column>
		    <el-table-column
		        prop="remarks"
		        label="Remarks">
		    </el-table-column>
		    <el-table-column
		        prop="balance"
		        label="Balance">
		        <template slot-scope="scope">
                    <span>{{ $nf.formatNumber(scope.row.balance) }}</span>
                </template>
		    </el-table-column>
		</el-table>
		<template  v-if = "timeDeposit.account_status != 'CLOSED'">
            <el-row :gutter = "5" class = "mt-20">
                <el-col :span = "4">
                    <label>Date</label>
                </el-col>
                <el-col :span = "12">
                    <el-date-picker v-model="openDate" @change = "changeDate" type="date" placeholder="Pick a date"> </el-date-picker>
                </el-col>
            </el-row>

			<el-row :gutter = "5" class = "mt-5">
                
				<el-col :span = "14">
					<label>Withdraw Amount</label>
					<el-input v-model="withdrawAmt"></el-input>
				</el-col>
				<el-col :span = "10">
					<label>Renew Amount</label>
					<!-- <el-input v-model="renewAmt" :disabled = "true"></el-input> -->
                    <el-input v-model="renewAmt"></el-input>
				</el-col>
			</el-row>
            <el-row>
                <el-col :span = "24">
                    <process-account
                        ref = "processAccount"
                        v-if = "timeDeposit && timeDeposit.member"
                        :page-data = "processData">
                    </process-account>
                </el-col>
            </el-row>
			<!-- <el-button class = "mt-10" type = "primary" @click = "showVoucher()" v-if = "timeDeposit.account_status == 'MATURED'">Process</el-button> -->
            <el-button class = "mt-10" type = "primary" @click = "showVoucher()">Process</el-button>
            <el-button class = "mt-10" type = "primary" @click = "print('print')">Print</el-button>
		</template>
		

		<voucher-view-form 
            :data-list = "genVoucher"
            v-if="isShowVoucher"
            :visible.sync="isShowVoucher"
            @close="isShowVoucher = false"
            @processvoucher="processVoucher">
        </voucher-view-form>
		
	</div>
</template>

<script>
window.noty = require('noty')
import Noty from 'noty'
import cloneDeep from 'lodash/cloneDeep' 
import _forEach from 'lodash/forEach'

import _message from '../../mixins/messageDialog.js'
import fileExport from '../../mixins/fileExport'

export default {
	props: {
		timedepositData: {
			type: Object,
			required: true
		},
		permission:{
			type: Object,
			required: false
		}
	},
	mixins: [_message, fileExport],
	data: function () {
        let processData = {memberData : this.timedepositData.member, accData : null}
		return {
			timeDeposit 	: this.timedepositData,
			tableLoading 	: false,
			savingsInterest : null,
			balanceAmt 		: 0,
			withdrawAmt 	: 0,
			renewAmt 		: 0,
			isShowVoucher 	: false,
			pageLoading 	: false,
            openDate        : moment(this.$systemDate),
            processData     : processData,
            genVoucher      : [],
            accToProcess    : []
		}
	},
	mounted(){
		setTimeout(() => {
			this.getCalculation(this.timeDeposit.accountnumber)
		}, 500)
	},
    computed: {
        transactionList(){   
        	let tdData = cloneDeep(this.timeDeposit)
        	let tdTransaction = tdData.transactions
            let transactionList = []
            _forEach(tdTransaction, trans =>{
            	transactionList.push(trans)
            })
            this.balanceAmt = cloneDeep(tdData.balance)

            if(this.savingsInterest){
            	let balance = Number(tdData.balance) + cloneDeep(Number(this.savingsInterest))
            	balance = this.$nf.numberFixed(balance, 2)
            	let arr = {
            		transaction_type 	: "(SAVINGS)",
            		amount 				: this.savingsInterest,
            		remarks 			: "Calculated with savings interest",
            		balance 			: balance
            	}
            	this.balanceAmt = balance
            	transactionList.push(arr)
            }
            
            return transactionList
        },
    },
	methods:{
        changeDate(val){
            let dt = this.$df.formatDate(this.$df.formatDate(val, "YYYY-MM-DD"), "X")
            let tdMature = this.$df.formatDate(this.$df.formatDate(this.timeDeposit.maturity_date, "YYYY-MM-DD"), "X")
            
            if(dt < tdMature){
                new Noty({
                    theme: 'relax',
                    type: "error",
                    layout: 'topRight',
                    text: 'Select date after or on maturity date.',
                    timeout: 2500
                }).show()

                return
            }

            this.getCalculation(this.timeDeposit.accountnumber, this.$df.formatDate(val, "YYYY-MM-DD"))
        },
		getCalculation(id, date = null){
			this.pageLoading = true

			this.savingsInterest = null
			this.$API.TimeDeposit.savingsCalculation(id, date)
            .then(result => {
            	let res = result.data
                this.savingsInterest = res.data

                setTimeout(() => {
                	this.withdrawAmt = this.balanceAmt
                }, 500)
            })
            .catch(err => {
                console.log(err)
            })
            .then(_ => {
                this.pageLoading = false
            })
		},
        print(type){
            if(this.transactionList.length == 0){
                new Noty({
                    theme: 'relax',
                    type: "error",
                    layout: 'topRight',
                    text: "No transaction to process.",
                    timeout: 3000
                }).show();
                return
            }
            console.log("Here")
            this.pageLoading = true

            let account = cloneDeep(this.timeDeposit)
            let accName = account.account_name
            if(account.member){
                accName = account.member.fullname
            }

            let data = {
                account    : account,
                account_no : account.accountnumber,
                account_name : accName,
                amount : account.amount,
                transaction : this.transactionList
            }

            this.$API.General.printList(data, type, 'TimeDeposit')
            .then(result => {
                let res = result.data
                if(type == 'pdf'){
                    this.exporter(type, 'Time Deposit Account', res)
                }
                else if(type == 'print'){
                    this.winPrint(res.data, 'Time Deposit Account')
                }
            })
            .catch(err => { console.log(err)})
            .then(_ => { this.pageLoading = false })

        },

        setVoucher(){
            this.accToProcess = []
            let list = []
            let debitTotal = 0
            let creditTotal = 0
            //Set tdaccount debit
            let arr = {particular_name: "Time Deposit", amount: parseFloat(this.timeDeposit.amount), type : "DEBIT" }
            list.push(arr)
            debitTotal += parseFloat(this.timeDeposit.amount)

            if(this.timeDeposit.transactions && this.timeDeposit.transactions.length > 0){
                _forEach(this.timeDeposit.transactions, tr => {
                    if(tr.transaction_type == 'TDINTEREST'){
                        let arr = {particular_name: "Interest Expense", amount: parseFloat(tr.amount), type : "DEBIT" }
                        list.push(arr)
                        debitTotal += parseFloat(tr.amount)
                    }
                })
            }

            // If has interest savings. Set as Interest Expense
            if(this.savingsInterest){
                let arr = {particular_name: "Interest Expense", amount: this.savingsInterest, type : "DEBIT" }
                list.push(arr)
                debitTotal += parseFloat(this.savingsInterest)
            }

            // If to withdraw, Set cash on hand
            if(Number(this.withdrawAmt) > 0){
                let arr = {particular_name: "Cash on Hand", amount: this.withdrawAmt, type : "CREDIT" }
                list.push(arr)
                creditTotal += parseFloat(this.withdrawAmt)
            }

            // If to renew, set TD account
            if(Number(this.renewAmt) > 0){
                let arr = {particular_name: "Time Deposit", amount: this.renewAmt, type : "CREDIT" }
                list.push(arr)
                creditTotal += parseFloat(this.renewAmt)
            }

            if(this.$refs && this.$refs.processAccount){
                let loanToPay = this.$refs.processAccount.loanToPayList
                let otherToPay = this.$refs.processAccount.otherToPay
                let setVoucherAccount = this.$ah.setVoucherAccount(loanToPay, otherToPay)
                console.log('setVoucherAccount', setVoucherAccount)
                if(!setVoucherAccount.success){
                    if(setVoucherAccount.error == "ERR_LOAN_BALANCE"){
                        this.showMessage('error', 'AMOUNT TO PAY is greater than LOAN BALANCE', 5000)
                        return false
                    }
                }
                else{
                    let dataVoucher = setVoucherAccount.data
                    let totalOtherToPay = dataVoucher.totalOtherToPay
                    creditTotal += parseFloat(totalOtherToPay)
                    let totalOtherToWithdraw = dataVoucher.totalOtherToWithdraw
                    debitTotal += parseFloat(totalOtherToWithdraw)
                    list = list.concat(dataVoucher.toPaySave)
                    this.accToProcess = dataVoucher.accToPaySave
                }
            }

            

            creditTotal = this.$nf.numberFixed(creditTotal, 2)
            debitTotal = this.$nf.numberFixed(debitTotal, 2)


            console.log('creditTotal', creditTotal, debitTotal)
            if(creditTotal != debitTotal){
                this.showMessage('error', 'VOUCHER is not balance', 5000)
                this.accToProcess = []
                return false
            }

            this.genVoucher = list

            return true
        },
        showVoucher(){
            let isset = this.setVoucher()
            if(isset){
                this.isShowVoucher = true
            }
        },
        getSavingsTransaction(){
        	let savings_transaction = null
        	if(this.savingsInterest){
        		savings_transaction = {
        			amount : this.savingsInterest,
        			transaction_type : 'TDINTEREST',
        			remarks : "Savings interest"
        		}
        	}
        	return savings_transaction;
        },
        processVoucher(data){
        	let savings_transaction = this.getSavingsTransaction()
        	this.pageLoading = true

        	let params = {
        		account_id : this.timeDeposit.accountnumber,
        		total_amount : this.balanceAmt,
        		savings_transaction : savings_transaction,
        		withdraw_amount : this.withdrawAmt,
        		renew_amount : this.renewAmt,
        		general_voucher : data,
                open_date : this.$df.formatDate(this.openDate, "YYYY-MM-DD"),
                otherAccToProcess : this.accToProcess,
        	}

        	this.$API.TimeDeposit.processAccount(params)
            .then(result => {
            	let res = result.data
            	if(res.success){
            		this.showMessage('success', "Time Deposit successfully processed.")
            	}else{
            		let msg = res.errorMessage
            		if(msg == ""){
            			msg = "Time Deposit successfully processed. Please report this to your technical support."
            		}
            		this.showMessage('error', msg)
            	}

            	this.$emit('afterprocess', {success : res.success})
            })
            .catch(err => {
                console.log(err)
            })
            .then(_ => {
                this.pageLoading = false
            })

        }
	},
	watch:{
		timedepositData: function(val){
			this.timeDeposit = val

			setTimeout(() => {
                this.openDate = moment(this.$systemDate)
				this.getCalculation(this.timeDeposit.accountnumber)


                this.processData.memberData = val.member
                this.loanToPay = []
                this.otherToPay = []
			}, 500)
		},
		withdrawAmt: function(val){
			let renew = Number(this.balanceAmt) - Number(val)
			this.renewAmt = this.$nf.numberFixed(renew, 2)
		}
	}
}
</script>

<style lang="scss">
  	@import '../../assets/site.scss';
  	@import '~noty/src/noty.scss';
</style>