<template>
     <div>
         <div class="pending-list">

                <div class="box box-solid">
                    <div class="box-header with-border">
                        <i class="fa fa-text-width"></i>

                        <h3 class="box-title">Loans Pending For Approval</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                         <el-row :gutter="20">
                             <el-col :span="17">
                                     <el-input
                                        v-model="search"
                                        size="mini"
                                        placeholder="Type to search"/>
                                    <el-table
                                        :data="ForApprovalLoans.filter(data => !search || data.fullname.toLowerCase().includes(search.toLowerCase()))"
                                        style="width: 100%">
                                        
                                         <el-table-column
                                            label="Acc. No."
                                            prop="account_no">
                                        </el-table-column>

                                        <el-table-column
                                            label="Loan Product"
                                            prop="product_name">
                                        </el-table-column>

                                        <el-table-column
                                            label="Name"
                                            prop="fullname">
                                        </el-table-column>

                                        <el-table-column
                                            label="Principal"
                                            prop="principal">
                                        </el-table-column>

                                         <el-table-column
                                            label="Date Applied"
                                            prop="date_created">
                                        </el-table-column>

                                        <el-table-column
                                        label="Option"
                                        >
                                             <template slot-scope="scope">
                                                <el-button
                                                size="mini"
                                                @click="handleEdit(scope.$index, scope.row)"
                                               >View Details</el-button>
                                             </template>
                                        </el-table-column>
                                       
                                    </el-table>
                             </el-col>

                             <el-col :span="7">
                                <div class="box box-solid">
                                            <div class="box-header with-border">
                                                <h5 class="box-title">Loan Details</h5>
                                            </div>
                                            <!-- /.box-header -->
                                            <div class="box-body box-profile">
                                                    <img class="profile-user-img img-responsive img-circle" alt="User profile picture">

                                                    <h3 class="profile-username text-center">{{loanprofile.fullname}}</h3>

                                                    <p class="text-muted text-center">Loan Applicant</p>

                                                    <ul class="list-group list-group-unbordered">
                                                        <li class="list-group-item">
                                                        <b>Loan Product</b> <a class="pull-right">{{loanprofile.product_name}}</a>
                                                        </li>
                                                        <li class="list-group-item">
                                                        <b>Principal</b> <a class="pull-right">{{loanprofile.principal}}</a>
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
                                                                    <td>{{loanprofile.principal}}</td>
                                                                    <td>{{loanprofile.credit_loan}}</td>
                                                                    </tr>

                                                                    <tr>
                                                                    <th scope="row">Interest</th>
                                                                    <td>0.00</td>
                                                                    <td>{{loanprofile.credit_interest}}</td>
                                                                    </tr>

                                                                    <tr>
                                                                    <th scope="row">Prepaid Int.</th>
                                                                    <td>0.00</td>
                                                                    <td>{{loanprofile.credit_preinterest}}</td>
                                                                    </tr>


                                                                    <tr>
                                                                    <th scope="row">Redemp. Ins.</th>
                                                                    <td>{{loanprofile.debit_redemption_ins}}</td>
                                                                    <td>{{loanprofile.redemption_insurance}}</td>
                                                                    </tr>

                                                                    <tr>
                                                                    <th scope="row">Service Charge</th>
                                                                    <td>0.00</td>
                                                                    <td>{{loanprofile.service_charge}}</td>
                                                                    </tr>

                                                                    <tr>
                                                                    <th scope="row">Savings (1%)</th>
                                                                    <td>0.00</td>
                                                                    <td>{{loanprofile.savings_retention}}</td>
                                                                    </tr>

                                                                    <tr>
                                                                    <th scope="row">Notary</th>
                                                                    <td>0.00</td>
                                                                    <td>{{loanprofile.notary_amount}}</td>
                                                                    </tr>

                                                                    <tr>
                                                                    <th scope="row">NET CASH</th>
                                                                    <td>0.00</td>
                                                                    <th scope="row">{{loanprofile.net_cash}}</th>
                                                                    </tr>
                                                                    
                                                                     <tr>
                                                                    <th scope="row">TOTAL =</th>
                                                                    <td>Thornton</td>
                                                                    <td>@fat</td>
                                                                    </tr>

                                                                </tbody>
                                                           </table>
                                                    </div>

                                                    <a href="#" @click="approveLoan()" class="btn btn-success btn-block"><b>Approve Loan Application</b></a>
                                             </div>
                                            <!-- /.box-body -->
                                </div>
                             </el-col>
                        </el-row>
                    </div>
            <!-- /.box-body -->
                </div>
                <div class="col-md-6">
                    eyow
                </div>

                <div class="col-md-6">

                </div>
        </div>
    </div>
</template>

<script>
window.noty = require('noty')
import Noty from 'noty'
import cloneDeep from 'lodash/cloneDeep'    
import _forEach from 'lodash/forEach'


export default {
    props: ['ForApprovalLoans'],
    data() {
        return {
            search: '',
            loanprofile: [],
            LoanToRenew: []
        }
    },



    methods:{

            handleEdit(index, row){

                this.loanprofile = row
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


            approveLoan()
            {
                let data = new FormData()

				let loandata = {
					evaluationFormss: this.loanprofile,
					loanToRenew: this.LoanToRenew == null ? null : {
						account_number: this.LoanToRenew.account_no,
						product_id:  this.LoanToRenew.loan_id,
					}
				}

				data.set('applyLoan', JSON.stringify(loandata))
				
				this.$API.Loan.approveLoan(data)
				.then(result=>{
					console.log("successresultx", result.data);

                    new Noty({
				    
				            type: 'error',
				            layout: 'topRight',
				            text: 'An error occured. Please try again or contact administrator',
				            timeout: 2500
                        }).show()
                     

				}).catch(err=>{
					console.log("apierror", err.message);
				})
            }


    }
}
</script>

<style>

</style>
