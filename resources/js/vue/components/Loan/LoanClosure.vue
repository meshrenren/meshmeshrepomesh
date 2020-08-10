<template>
    <div>
        <div class="box box-default">
                <div class="box-header with-border"><h3 class="box-title">Loan Accounts Closure</h3></div>

                <div class="box-body">
                   
                    <el-form :model="loanDetails" ref="paymentForm" label-width="160px" :label-position="labelPosition">
                        <el-row :gutter="20">
                            <el-col :span="10">
                                <el-form-item label="Maker">
                                    <el-input type="text" v-model="loanDetails.maker"></el-input>
                                </el-form-item>

                                <el-form-item label="Loan">
                                    <el-input type="text" v-model="loanDetails.loan_product_name"></el-input>
                                </el-form-item>

                                <el-form-item label="Account Number">
                                    <el-input type="text" v-model="loanDetails.account_number"></el-input>
                                </el-form-item>

                                <el-form-item label="Principal">
                                    <el-input type="text" v-model="loanDetails.principal"></el-input>
                                </el-form-item>

                                <el-form-item label="Principal Balance">
                                    <el-input type="text" v-model="loanDetails.principal_balance"></el-input>
                                </el-form-item>

                                <el-form-item label="Interest Earned">
                                    <el-input type="text" v-model="loanDetails.interest_balance"></el-input>
                                </el-form-item>

                                <el-form-item label="Prepaid Interest">
                                    <el-input type="text" v-model="loanDetails.prepaid_interest_balance"></el-input>
                                </el-form-item>

                                <el-form-item label="Total Payable">
                                    <el-input type="text" v-model="loanDetails.total_payable"></el-input>
                                </el-form-item>
                            </el-col>
                            
                        </el-row>

                         <el-row :gutter="20">
                            <el-col :span="10">
                                <div>
                                    <el-button style="float:right;" type = "primary"  ref = "newLoan" >Close Loan Account</el-button> 
                                    <el-button @click="showSearchLoanModal = true" style="float:right; margin-right:20px;" type = "primary"  ref = "newLoan" >Find</el-button>
                                </div>
                            </el-col>
                         </el-row>
                    </el-form>
                </div>
        </div>


        <search-current-loans :show-modal = "showSearchLoanModal" :data-includes = "['shareaccount']" @select="populateField" @close = "showSearchLoanModal = false" >
	  	</search-current-loans>
    </div>
</template>


<script>
import sumBy from 'lodash/sumBy' 
export default {

    data(){

        return {
            labelPosition: 'left',
            loanDetails: {
                maker: '',
                account_number: '',
                loan_product_name: null,
                principal: null,
                principal_balance: null,
                interest_balance: null,
                prepaid_interest_balance: null,
                total_payable: 0
            },
            showSearchLoanModal: false
        }
    },


    methods: {
        populateField(loan) {
            console.log("catched", loan)
            this.loanDetails.maker = loan.member.fullname;
            this.loanDetails.account_number = loan.account_no;
            this.loanDetails.loan_product_name = loan.product.product_name;
            this.loanDetails.principal = loan.principal;
            this.loanDetails.principal_balance = loan.principal_balance;
            this.loanDetails.prepaid_interest_balance = loan.prepaid_accum;

        //var objects = [{ 'n': 4 }, { 'n': 2 }, { 'n': 8 }, { 'n': 6 }];
        //this.loanDetails.interest_balance
            let PreviousLoanAccumulatedInterest =  sumBy(loan.loanTransaction, o => {
                    return Number(o.interest_earned);
            });

            let vm = this;
             this.$API.Loan.getCurrentLoanInterestSincePreviousTransaction(loan.account_no, Number(loan.product.int_rate))
            .then(result => {
               
               vm.loanDetails.interest_balance = parseFloat(Number(PreviousLoanAccumulatedInterest) + Number(result.data.interest_earned)).toFixed(2)
               vm.loanDetails.total_payable = parseFloat(Number(vm.loanDetails.interest_balance) + Number(vm.loanDetails.principal_balance) + Number(vm.loanDetails.prepaid_interest_balance)).toFixed(2)
               //console.log(result.data)
            })
            .catch(err => {
                console.log(err)
            })
                       



        }
    }
  
}
</script>
