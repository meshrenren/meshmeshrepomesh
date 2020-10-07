<template>
	<div class="process-other mt-10" v-loading = "loading">
		<el-button type = "info" @click = "otherParticulars()">OTHER</el-button>
        <table class="table table-bordered tbl-list mt-10" v-if = "otherToPay.length > 0">
            <tbody>
                <tr>
                    <th></th>
                    <th>DEBIT</th>
                    <th>CREDIT</th>
                </tr>
                <tr v-for="item in otherToPay">
                    <th scope="row">
                        <el-select class = "mt-5" v-model="item.particular_id" filterable placeholder="Select Particular">
                            <el-option
                              v-for="item in otherList"
                              :key="item.id"
                              :label="item.name"
                              :value="item.id">
                            </el-option>
                        </el-select>
                    </th>
                    <td> 
                        <el-input class = "mt-5" type="number" :min = "0" v-model="item.amountToWithdraw"></el-input>
                    </td>
                    <td> 
                        <el-input class = "mt-5" type="number" :min = "0" v-model="item.amountToPay"></el-input>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Show only if Regular  -->
        <el-button type = "info" @click = "getMemberAccount()">ACCOUNTS</el-button>
        <table class="table table-bordered tbl-list mt-10" v-if = "loanToPayList.length > 0">
            <tbody>
                <tr>
                    <th></th>
                    <th>Balance</th>
                    <th>Withdraw</th>
                    <th>Deposit/Payment</th>
                </tr>
                <tr v-for="item in loanToPayList">
                    <th scope="row">{{ item.product_name }}</th>
                    <td>  
                        <span v-if = "item.type == 'LOAN'">
                            <template v-if = "!accData || (accData && accData.loan_id != item.product_id )">
                                {{ $nf.formatNumber(item.principal, 2)  }} ({{ $nf.formatNumber(item.balance, 2) }})
                            </template>
                        </span>
                        <span v-else>
                            {{ $nf.formatNumber(item.balance, 2) }}
                        </span>
                    </td>
                    <td>
                        <el-input v-if = "item.type != 'LOAN'" :disabled = "Number(item.balance) <= 0" class = "mt-5" type="number" :min = "0" :max = "Number(item.balance)" v-model="item.amountToWithdraw"></el-input>
                    </td>
                    <td>
                        <el-input v-if = "item.type == 'LOAN'" class = "mt-5" type="number" :min = "0" v-model="item.amountToPay" :disabled = "Number(item.balance) <= 0" :max = "Number(item.balance)"></el-input>
                        <el-input v-else class = "mt-5" type="number" :min = "0" v-model="item.amountToPay"></el-input>
                    </td>
                </tr>
            </tbody>
        </table>
	</div>
</template>
<script>

window.noty = require('noty')
import Noty from 'noty'  
import cloneDeep from 'lodash/cloneDeep'    
import _forEach from 'lodash/forEach'
import _uniq from 'lodash/uniq'

import accountsMixins from '../mixins/accountsMixins.js'

export default {
    props: {
        pageData:{
            type: Object,
            required: true,
        }
    },
    mixins: [accountsMixins],
    data(){

    	return{
            otherList       : [],
    		otherToPay      : [],
            loanToPayList   : [],
            memberData      : this.pageData.memberData,
            accData         : this.pageData.accData,
            loading         : false
    	}
    },
    mounted(){
        this.getParticulars()
    },
    methods:{
        getParticulars(){
            this.loading = true
            
            this.$API.General.getParticularsByName(null, ['OTHERS'])
            .then(result => {
                let res = result.data
                this.otherList = res.data
            })
            .catch(err => {
                console.log(err)
            })
            .then(_ => {
                this.loading = false
            })

        },
    	otherParticulars(){
            let arr = {particular_id : null, amountToPay : null, amountToWithdraw : null}
            this.otherToPay.push(arr)
        },
        getMemberAccount(){
            this.loading = true
            this.loanToPayList = []
            let member_id = this.memberData.id
            this.$API.Payment.getMemberAccount(member_id)
            .then(result => {
                let res = result.data
                let list = []
                this.mergeAccount(res.loanAccounts, res.savingsAccounts, res.shareAccounts)
            })
            .catch(err => {
                console.log(err)
            })
            .then(_ => { 
                this.loading = false
            })
        },
        mergeAccount(loans = null, savings = null, shares = null){
            let notIncLoan = null
            if(this.accData && this.accData.loan_id){
                notIncLoan = [this.accData.loan_id]
            }
            let allAccounts = this.$ah.mergAllAccounts(loans, savings, shares, null, notIncLoan)
            this.loanToPayList = allAccounts
        },
    },
    watch: {
        'pageData.memberData' : function(val){     
            this.memberData = val
        },
        'pageData.accData' : function(val){     
            this.accData = val
        },
        'dataOtherToPay' : function(val){     
            this.otherToPay = val
        },
        'dataLoanToPay' : function(val){     
            this.loanToPayList = val
        },

    }
}
</script>

