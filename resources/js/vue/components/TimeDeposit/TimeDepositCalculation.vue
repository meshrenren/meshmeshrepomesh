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
		<template  v-if = "timeDeposit.account_status == 'MATURED'">
			<el-row :gutter = "5" class = "mt-20">
				<el-col :span = "14">
					<label>Withdraw Amount</label>
					<el-input v-model="withdrawAmt"></el-input>
				</el-col>
				<el-col :span = "10">
					<label>Renew Amount</label>
					<el-input v-model="renewAmt" :disabled = "true"></el-input>
				</el-col>
			</el-row>
			<el-button class = "mt-10" type = "primary" @click = "showVoucher()" v-if = "timeDeposit.account_status == 'MATURED'">Process</el-button>
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
	mixins: [_message],
	data: function () {

		return {
			timeDeposit 	: this.timedepositData,
			tableLoading 	: false,
			savingsInterest : null,
			balanceAmt 		: 0,
			withdrawAmt 	: 0,
			renewAmt 		: 0,
			isShowVoucher 	: false,
			pageLoading 	: false

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
        genVoucher(){
        	let list = []
        	//Set tdaccount debit
        	let arr = {particular_name: "Time Deposit", amount: this.timeDeposit.balance, type : "DEBIT" }
        	list.push(arr)

        	// If has interest savings. Set as Interest Expense
        	if(this.savingsInterest){
        		let arr = {particular_name: "Interest Expense", amount: this.savingsInterest, type : "DEBIT" }
        		list.push(arr)
        	}

        	// If has interest savings. Set as Interest Expense
        	if(Number(this.withdrawAmt) > 0){
        		let arr = {particular_name: "Cash on Hand", amount: this.withdrawAmt, type : "CREDIT" }
        		list.push(arr)
        	}

        	// If has interest savings. Set as Interest Expense
        	if(Number(this.renewAmt) > 0){
        		let arr = {particular_name: "Time Deposit", amount: this.renewAmt, type : "CREDIT" }
        		list.push(arr)
        	}

        	return list
        }
    },
	methods:{
		getCalculation(id){
			this.pageLoading = true

			this.savingsInterest = null
			this.$API.TimeDeposit.savingsCalculation(id)
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
        showVoucher(){
        	this.isShowVoucher = true
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
        		general_voucher : data
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
				this.getCalculation(this.timeDeposit.accountnumber)
			}, 500)
		},
		withdrawAmt: function(val){
			let renew = Number(this.balanceAmt) - Number(val)
			this.renewAmt = renew
		}
	}
}
</script>

<style lang="scss">
  	@import '../../assets/site.scss';
  	@import '~noty/src/noty.scss';
</style>