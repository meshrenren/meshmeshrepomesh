<template>
    <div class="pending-list" >
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fa fa-text-width"></i>

                <h3 class="box-title">Loans Pending For Approval</h3>
            </div>
            <div class="box-body" v-loading = pageLoading>
                <el-row :gutter="20">
                    <el-col :span="14" class = "loan-list">
                        <el-input v-model="search" size="mini" placeholder="Type to search"/>
                        <el-table
                            :data="toApproveLoan.filter(data => !search || (data.member && data.member.fullname.toLowerCase().includes(search.toLowerCase())))"
                            style="width: 100%"
                            max-height = "1000px">
                            
                             <el-table-column
                                label="Acc. No."
                                prop="account_no">
                            </el-table-column>

                            <el-table-column
                                label="Loan Product"
                                prop="product.product_name">
                            </el-table-column>

                            <el-table-column
                                label="Name"
                                prop="member.fullname">
                            </el-table-column>

                            <el-table-column
                                label="Principal"
                                prop="principal">
                            </el-table-column>

                             <el-table-column
                                label="Date Applied"
                                prop="date_created">
                            </el-table-column>

                            <el-table-column label="Option">
                                <template slot-scope="scope">
                                    <el-button size="mini" type = "primary" @click="handleEdit(scope.$index, scope.row)" >View</el-button>
                                    <el-button class = "mt-5" size="mini" type="danger" @click="cancelLoan(scope.$index, scope.row)"
                                   >Cancel</el-button>
                                 </template>
                            </el-table-column>
                           
                        </el-table>
                    </el-col>

                    <el-col :span="10">
                        <div class="box box-solid">
                            <div class="box-header with-border">
                                <h5 class="box-title">Loan Details</h5>
                            </div>
                            <div class="box-body box-profile">
                                <!-- <img class="profile-user-img img-responsive img-circle" alt="User profile picture"> -->

                                <h3 class="profile-username text-center">
                                    <template v-if = "loanprofile && loanprofile.member">
                                        {{loanprofile.member.fullname}}
                                    </template>
                                </h3>

                                <p class="text-muted text-center">Loan Applicant</p>

                                <ul class="list-group list-group-unbordered">
                                    <li class="list-group-item">
                                        <b>Loan Product</b> 
                                        <a class="pull-right">
                                            <template v-if = "loanprofile && loanprofile.product">
                                                {{loanprofile.product.product_name}}
                                            </template>
                                        </a>
                                    </li>
                                    <li class="list-group-item">
                                        <b>Principal</b> <a class="pull-right">{{ $nf.formatNumber(loanprofile.principal, 2) }}</a>
                                    </li>
                                    <li class="list-group-item">
                                        <b>Date Created</b> <a class="pull-right">{{loanprofile.date_created}}</a>
                                    </li>
                                </ul>

                                <div class="row">
                                    <table class="table table-striped table-dark">
                                        <thead>
                                            <tr>
                                            <th scope="col">Description</th>
                                            <th scope="col">Debit</th>
                                            <th scope="col">Credit</th>
                                            
                                            </tr>
                                        </thead>
                                        <tbody>

                                            <tr>
                                            <th scope="row">LOAN</th>
                                            <td>{{ $nf.formatNumber(loanprofile.principal, 2) }}</td>
                                            <td>{{ $nf.formatNumber(loanprofile.credit_loan, 2) }}</td>
                                            </tr>

                                            <tr>
                                            <th scope="row">Interest</th>
                                            <td>0.00</td>
                                            <td>{{ $nf.formatNumber(loanprofile.credit_interest, 2) }}</td>
                                            </tr>

                                            <tr>
                                            <th scope="row">Prepaid Int.</th>
                                            <td>{{ $nf.formatNumber(loanprofile.debit_preinterest, 2) }}</td>
                                            <td>{{ $nf.formatNumber(loanprofile.credit_preinterest, 2) }}</td>
                                            </tr>


                                            <tr>
                                            <th scope="row">Redemp. Ins.</th>
                                            <td>{{ $nf.formatNumber(loanprofile.debit_redemption_ins, 2) }}</td>
                                            <td>{{ $nf.formatNumber(loanprofile.redemption_insurance, 2) }}</td>
                                            </tr>

                                            <tr>
                                            <th scope="row">Service Charge</th>
                                            <td>0.00</td>
                                            <td>{{ $nf.formatNumber(loanprofile.service_charge, 2) }}</td>
                                            </tr>

                                            <tr>
                                            <th scope="row">Retention</th>
                                            <td>0.00</td>
                                            <td>{{ $nf.formatNumber(loanprofile.savings_retention, 2) }}</td>
                                            </tr>

                                            <tr>
                                            <th scope="row">Notary</th>
                                            <td>0.00</td>
                                            <td>{{ $nf.formatNumber(loanprofile.notary_amount, 2) }}</td>
                                            </tr>

                                            <tr>
                                            <th scope="row">NET CASH</th>
                                            <td>0.00</td>
                                            <th scope="row">{{ $nf.formatNumber(loanprofile.net_cash, 2) }}</th>
                                            </tr>

                                        </tbody>
                                    </table>
                                    <template v-if = "loanprofile && loanprofile.member">

                                        <el-button class = "mt-10" type = "info" @click = "otherParticulars()">Other</el-button>
                                        <table class="table table-striped table-dark" v-if = "otherToPay.length > 0">
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
                                                    <el-input class = "mt-5" type="number" :min = "0" v-model="item.amountToPay"></el-input>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Show only if Regular  -->
                                        <el-button class = "mt-10" type = "info" @click = "getMemberAccount()">Account to deduct</el-button>
                                        <table class="table table-striped table-dark" v-if = "loanToPayList.length > 0">
                                            <tr v-for="item in loanToPayList">
                                                <th scope="row">{{ item.product_name }}</th>
                                                <td>
                                                    <template v-if = "item.type == 'LOAN'">
                                                        {{ $nf.formatNumber(item.principal, 2)  }} ({{ $nf.formatNumber(item.balance, 2) }})
                                                    </template>
                                                    <template v-else>
                                                        {{ $nf.formatNumber(item.balance, 2) }}
                                                    </template>
                                                </td>
                                                <td>
                                                    <el-input v-if = "item.type == 'LOAN'" class = "mt-5" type="number" :min = "0" v-model="item.amountToPay" :disabled = "Number(item.principal_balance) <= 0" :max = "Number(item.principal_balance)"></el-input>
                                                    <el-input v-else class = "mt-5" type="number" :min = "0" v-model="item.amountToPay"></el-input>
                                                </td>
                                            </tr>
                                        </table>
                                    </template>
                                    
                                </div>
                                <el-button class = "mt-10" type = "primary" @click = "releaseVoucher()">Release Loan</el-button>
                                <!-- <el-button class = "mt-10" type = "primary" @click = "saveLoan()">Save Loan Without Release</el-button> -->
                                <!-- <el-button class = "mt-10" type = "primary" @click = "approveLoan()">Approve Loan Application</el-button> -->
                            </div>
                        </div>
                     </el-col>
                </el-row>
            </div>
        </div>
        <voucher-view-form 
            :data-list = "voucherList"
            :gv-required = "true"
            :date-transact = "loanprofile.release_date"
            v-if="isShowVoucher"
            :visible.sync="isShowVoucher"
            @close="isShowVoucher = false"
            @processvoucher="processVoucher">
        </voucher-view-form>

        <enter-voucher 
            :gv-required = "true"
            :date-transact = "loanprofile.release_date"
            v-if="isEnterVoucher"
            :visible.sync="isEnterVoucher"
            @close="isEnterVoucher = false"
            @processentervoucher="processentervoucher">
        </enter-voucher>
       
    </div>
</template>

<script>
window.noty = require('noty')
import Noty from 'noty'
import cloneDeep from 'lodash/cloneDeep'    
import _forEach from 'lodash/forEach'


export default {
    props: ['ForApprovalLoans', 'pageData'],
    data() {
        return {
            search          : '',
            loanprofile     : [],
            LoanToRenew     : [],
            isShowVoucher   : false,
            toApproveLoan   : this.ForApprovalLoans,
            pageLoading     : false,
            voucherList     : [],
            particulars     : this.pageData.particulars,
            loanToPaySave   : [],
            loanToPayList   : [],
            otherToPay      : [],
            otherList       : [],
            isEnterVoucher  : false
        }
    },
    created(){
        console.log('created')
        this.otherList = cloneDeep(this.particulars).filter(fn => {
            return fn.category == "OTHERS" && ["Interest on Loans", "Service Fee", "Unearned Interest", "Unearned Interest", "Retention", "Ben Life Redemption Insurance", "Notarial Fee", "Cash on Hand"].indexOf(Number(fn.name)) < 0
        })
    },

    methods:{

        setVoucher(){
            this.voucherList = []

            let list = []
            //Set product name
            let arr = {particular_name: this.loanprofile.product.product_name, amount: this.loanprofile.principal, type : "DEBIT", account_no : this.loanprofile.account_no }
            list.push(arr)
            arr = {particular_name: this.loanprofile.product.product_name, amount: this.loanprofile.credit_loan, type : "CREDIT", account_no : this.loanprofile.account_no }
            list.push(arr)

            //Interest on Loans
            if(this.loanprofile.credit_interest && Number(this.loanprofile.credit_interest) > 0){
                arr = {particular_name: "Interest on Loans", amount: this.loanprofile.credit_interest, type : "CREDIT" }
                list.push(arr)
            }

            //Service Fee
            if(this.loanprofile.service_charge && Number(this.loanprofile.service_charge) > 0){
                arr = {particular_name: "Service Fee", amount: this.loanprofile.service_charge, type : "CREDIT" }
                list.push(arr)
            }

            //Unearned Interest
            if((this.loanprofile.debit_preinterest && Number(this.loanprofile.debit_preinterest) > 0)  || (this.loanprofile.credit_preinterest && Number(this.loanprofile.credit_preinterest) > 0)){
                arr = {particular_name: "Unearned Interest", amount: this.loanprofile.debit_preinterest, type : "DEBIT" }
                list.push(arr)
                arr = {particular_name: "Unearned Interest", amount: this.loanprofile.credit_preinterest, type : "CREDIT" }
                list.push(arr)
            }

            //Retention
            if(this.loanprofile.savings_retention && Number(this.loanprofile.savings_retention) > 0){
                arr = {particular_name: "Retention", amount: this.loanprofile.savings_retention, type : "CREDIT" }
                list.push(arr)
            }

            //Ben Life Redemption Insurance
            if((this.loanprofile.debit_redemption_ins && Number(this.loanprofile.debit_redemption_ins) > 0)  || (this.loanprofile.redemption_insurance && Number(this.loanprofile.redemption_insurance) > 0)){
                arr = {particular_name: "Ben Life Redemption Insurance", amount: this.loanprofile.debit_redemption_ins, type : "DEBIT" }
                list.push(arr)
                arr = {particular_name: "Ben Life Redemption Insurance", amount: this.loanprofile.redemption_insurance, type : "CREDIT" }
                list.push(arr)
            }

            //Notarial Fee
            if(this.loanprofile.notary_amount && Number(this.loanprofile.notary_amount) > 0){
                arr = {particular_name: "Notarial Fee", amount: this.loanprofile.notary_amount, type : "CREDIT" }
                list.push(arr)
            }

            let cashOnHand = this.loanprofile.net_cash
            this.loanToPaySave = []
            let totalOtherToPay = 0
            if(this.loanToPayList && this.loanToPayList.length > 0){
                let hasLimitLoan = false
                _forEach(this.loanToPayList, la =>{
                    if(la.amountToPay && la.amountToPay > 0){
                        if(parseFloat(la.amountToPay)  > parseFloat(la.balance) ){
                            hasLimitLoan = true
                        }
                        else{
                           //get Pi
                            let getPi = this.particulars.find(fn => Number(fn.id) == Number(la.particular_id))
                            if(getPi){
                                arr = {particular_name: getPi.name, amount: la.amountToPay, type : "CREDIT", account_no :  la.account_no, account_type : la.type}
                                list.push(arr)
                                this.loanToPaySave.push(la)

                                totalOtherToPay += parseFloat(la.amountToPay)
                            } 
                        }
                        
                    }
                })

                if(hasLimitLoan){
                    new Noty({
                        type: 'error',
                        layout: 'topRight',
                        text: 'AMOUNT TO PAY is greater than LOAN BALANCE',
                        timeout: 5000
                    }).show()
                    return false
                }
            }

            if(this.otherToPay && this.otherToPay.length > 0){
                _forEach(this.otherToPay, la =>{
                    if(la.particular_id && la.amountToPay && la.amountToPay > 0){
                        //get Pi
                        let getPi = this.particulars.find(fn => Number(fn.id) == Number(la.particular_id))
                        if(getPi){
                            arr = {particular_name: getPi.name, amount: la.amountToPay, type : "CREDIT", account_type : "OTHERS"}
                            list.push(arr)

                            totalOtherToPay += parseFloat(la.amountToPay)
                        }
                        
                    }
                })
            }


            if(totalOtherToPay > cashOnHand){
                new Noty({
                    type: 'error',
                    layout: 'topRight',
                    text: 'NEW ADDED PARTICULARS TO DEDUCT is greater than NET CASH',
                    timeout: 5000
                }).show()
                return false
            }
            cashOnHand = cashOnHand - totalOtherToPay
            cashOnHand = parseFloat(cashOnHand).toFixed(2)
            console.log("cashOnHand", cashOnHand)


            //Cash on Hand
            arr = {particular_name: "Cash on Hand", amount: cashOnHand, type : "CREDIT" }
            list.push(arr)

            this.voucherList = list
            return true
        },
        getMemberAccount(){
            this.pageLoading = true
            this.loanToPayList = []
            this.loanToPaySave = []
            let member_id = this.loanprofile.member_id
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
                this.pageLoading = false
            })
        },
        mergeAccount(loans = null, savings = null, shares = null){
            let allAccounts = []

            if(shares && shares.length > 0){
                _forEach(shares, rs =>{

                    let arr = {}
                    arr.member_id = rs.fk_memid
                    arr.key = "SHARE_" + rs.particular_id
                    arr.account_no = rs.accountnumber
                    arr.product_id = rs.fk_share_product
                    arr.product_name = rs.product.name
                    arr.type = "SHARE"
                    arr.balance = parseFloat(rs.balance).toFixed(2)
                    arr.particular_id = rs.product.particular_id
                    arr.amountToPay = null

                    allAccounts.push(arr)
                })
            }

            let savingsData = null
            if(savings && savings.length > 0){
                _forEach(savings, rs =>{
                    savingsData = rs

                    let arr = {}
                    arr.member_id = rs.member_id
                    arr.key = "SAVINGS_" + rs.particular_id
                    arr.account_no = rs.account_no
                    arr.product_id = rs.saving_product_id
                    arr.product_name = rs.product.description
                    arr.type = "SAVINGS"
                    arr.balance = parseFloat(rs.balance).toFixed(2)
                    arr.particular_id = rs.product.particular_id
                    arr.amountToPay = null

                    allAccounts.push(arr)
                })
            }

            if(loans && loans.length > 0){
                _forEach(loans, rs =>{
                    if(Number(rs.principal_balance) > 0){ 
                        let arr = {}
                        arr.member_id = rs.member_id
                        arr.key = "LOAN_" + rs.particular_id
                        arr.account_no = rs.account_no
                        arr.product_id = rs.loan_id
                        arr.product_name = rs.product.product_name
                        arr.type = "LOAN"
                        arr.balance = parseFloat(rs.principal_balance).toFixed(2)
                        arr.particular_id = rs.product.particular_id
                        arr.principal = parseFloat(rs.principal).toFixed(2)
                        arr.amountToPay = null

                        allAccounts.push(arr)
                    }
                })
            }

            

            this.loanToPayList = allAccounts
            
        },
        otherParticulars(){
            let arr = {particular_id : null, amountToPay : null}
            this.otherToPay.push(arr)
        },

        handleEdit(index, row){

            this.loanprofile = row
            this.loanToPayList = []
            this.loanToPaySave = []
            let vm = this;
            this.$API.Loan.getLatestLoan(row.loan_id, row.member_id)
    		.then(result => {
    			let res = result.data
				console.log("resRES", res)
				vm.LoanToRenew = res.data.latestLoan;
    		//	this.calculateLoan(getProduct, res.data)
    			
    		//	this.isLoading = false
			}).catch(err => {
				console.log(err)
			//	this.isLoading = false
            })


        },
        releaseVoucher(){
            let isset = this.setVoucher()
            if(isset){
                this.isShowVoucher = true
            }
        },
        processVoucher(data){
            this.approveLoan(data.gv_num, data.voucher_entries, data.transaction_date)
        },
        cancelLoan(index, row)
        {
            this.$swal({
                title: 'Cancel Loan',
                text: "Are you sure you want to cancel applied loan?",
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
                    this.loanprofile = row
                    let vm = this;
                    let params = {
                        status : "Cancel",
                        loanAccount : row
                    }
                    this.pageLoading = true
                    this.$API.Loan.updateLoanStatus(params)
                    .then(result => {
                        let res = result.data
                        if(res.success){
                            let loanData = res.data
                            let fnIndex = this.toApproveLoan.findIndex(rs => { return rs.account_no == loanData.account_no })
                            if(fnIndex >= 0){
                                this.toApproveLoan.splice(fnIndex, 1)
                            }
                        }
                        else{
                            new Noty({
                                type: 'error',
                                layout: 'topRight',
                                text: 'Loan not successfully cancelled',
                                timeout: 2500
                            }).show()
                        }
                        console.log("resRES", res)
                    }).catch(err => {
                        console.log(err)
                    })
                    .then(_ => { 
                        this.pageLoading = false
                    })
                }
            })

            
        },
        approveLoan(gvNumber, voucherDetails, transaction_date)
        {
            /*if(!this.gvNumber){
                new Noty({
                    type: 'error',
                    layout: 'topRight',
                    text: 'Please enter GV Number',
                    timeout: 2500
                }).show()
                return
            }*/

            //let data = new FormData()

            this.$swal({
                title: 'Post Release Loan',
                text: "Are you sure you want to release loan? This will automatically posted.",
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: '#d33',
                confirmButtonText: 'Proceed',
                focusConfirm: false,
                focusCancel: true,
                cancelButtonText: 'Cancel',
                reverseButtons: true,
            }).then(result => {
                if (result.value) {
        			let loandata = {
        				evaluationFormss: this.loanprofile,
        				loanToRenew: this.LoanToRenew == null ? null : {
        					account_number: this.LoanToRenew.account_no,
        					product_id:  this.LoanToRenew.loan_id,
        				},
                        voucherDetails : voucherDetails,
                        gv_num : gvNumber,
                        otherLoanToPay : this.loanToPaySave,
                        transaction_date : transaction_date
        			}

        			//data.set('applyLoan', JSON.stringify(loandata))
                    this.pageLoading = true
        			
        			this.$API.Loan.approveLoan(loandata)
        			.then(result=>{
                        let res = result.data
        				console.log("successresultx", result.data);
                        if(res.success){
                            new Noty({
                                type: 'success',
                                layout: 'topRight',
                                text: 'Loan successfully approved',
                                timeout: 2500
                            }).show()
                            location.reload()
                        }
                        else{
                            new Noty({
                                type: 'error',
                                layout: 'topRight',
                                text: 'Loan not successfully approved. Please try again or contact administrator',
                                timeout: 2500
                            }).show()
                        }
                         

        			}).catch(err=>{
        				console.log("apierror", err);
                         new Noty({
                            type: 'error',
                            layout: 'topRight',
                            text: 'An error occured. Please try again or contact administrator',
                            timeout: 2500
                        }).show()
        			}).then(_ => { 
                        this.pageLoading = false
                    })
                }
            })
        },

        saveLoan(){
            this.isEnterVoucher = true
        },
        processentervoucher(data){
            this.$swal({
                title: 'Save Loan',
                text: "Are you sure you want to save loan? Use this only for old loans that are not included on loan migration but in the release data is in General Voucher",
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
                    let loandata = {
                        evaluationForm: this.loanprofile,
                        gv_num : data.gv_num,
                        remarks : data.remarks,
                        transaction_date : data.transaction_date
                    }

                    //data.set('applyLoan', JSON.stringify(loandata))
                    this.pageLoading = true
                    
                    this.$API.Loan.saveLoan(loandata)
                    .then(result=>{
                        let res = result.data
                        console.log("successresultx", result.data);
                        if(res.success){
                            new Noty({
                                type: 'success',
                                layout: 'topRight',
                                text: 'Loan successfully approved',
                                timeout: 2500
                            }).show()
                            location.reload()
                        }
                        else{
                            new Noty({
                                type: 'error',
                                layout: 'topRight',
                                text: 'Loan not successfully approved. Please try again or contact administrator',
                                timeout: 2500
                            }).show()
                        }
                         

                    }).catch(err=>{
                        console.log("apierror", err);
                         new Noty({
                            type: 'error',
                            layout: 'topRight',
                            text: 'An error occured. Please try again or contact administrator',
                            timeout: 2500
                        }).show()
                    }).then(_ => { 
                        this.pageLoading = false
                    })
                }
            });

            
        }



    }
}
</script>

<style lang="scss">
.pending-list{
    .loan-list{
        .el-button{
            margin: 0 auto;
            display: block;
        }
    }
    
}
</style>
