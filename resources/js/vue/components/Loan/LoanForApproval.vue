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
                                        <process-account
                                            ref = "processAccount"
                                            v-if = "loanprofile && loanprofile.member"
                                            :page-data = "processData">
                                        </process-account>
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

import accountsMixins from '../../mixins/accountsMixins.js'
import _message from '../../mixins/messageDialog.js'

export default {
    props: ['ForApprovalLoans', 'pageData'],
    data() {
        let processData = {memberData : null, accData : null}
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
            otherList       : [],
            isEnterVoucher  : false,
            processData     : processData
        }
    },
    mixins: [accountsMixins, _message],
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

            let cashOnHand = parseFloat(this.loanprofile.net_cash)
            this.loanToPaySave = []
            let totalOtherToPay = 0
            let totalOtherToWithdraw = 0


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
                    totalOtherToPay = dataVoucher.totalOtherToPay
                    totalOtherToWithdraw = dataVoucher.totalOtherToWithdraw
                    list = list.concat(dataVoucher.toPaySave)
                    this.loanToPaySave = dataVoucher.accToPaySave
                }
            }

            cashOnHand = parseFloat(cashOnHand) + parseFloat(totalOtherToWithdraw)

            if(totalOtherToPay > cashOnHand){
                this.showMessage('error', 'NEW ADDED PARTICULARS TO DEDUCT is greater than NET CASH.', 5000)
                return false
            }

            cashOnHand = cashOnHand - totalOtherToPay
            cashOnHand = parseFloat(cashOnHand).toFixed(2)

            //Cash on Hand
            arr = {particular_name: "Cash on Hand", amount: cashOnHand, type : "CREDIT" }
            list.push(arr)

            this.voucherList = list
            return true
        },

        handleEdit(index, row){

            this.loanprofile = row
            this.processData.memberData = row.member
            this.processData.accData = row
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
                    this.showMessage('success', 'Loan successfully approved.', 3000)
                    location.reload()
                }
                else{
                    if(res.status == 'ERROR_HASGV'){
                        this.showMessage('error', 'GV Number already existed and posted.', 5000)
                    }else{
                        this.showMessage('error', 'Loan not successfully approved. Please try again or contact administrator.', 3000)
                    }
                }
                 

			}).catch(err=>{
				console.log("apierror", err);
                this.showMessage('error', 'An error occured. Please try again or contact administrator.', 3000)
			}).then(_ => { 
                this.pageLoading = false
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
