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

              <h3 class="profile-username text-center">Nina Mcintire</h3>

              <p class="text-muted text-center">Loan Applicant</p>

              <ul class="list-group list-group-unbordered">
                <li class="list-group-item">
                  <b>Loan Product</b> <a class="pull-right">{{loanprofile.product_name}}</a>
                </li>
                <li class="list-group-item">
                  <b>Principal</b> <a class="pull-right">50,000.00</a>
                </li>
                <li class="list-group-item">
                  <b>Date Created</b> <a class="pull-right">9/22/2019</a>
                </li>
              </ul>

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
					console.log("successresult", result.data);


				}).catch(err=>{
					console.log("apierror", err.message);
				})
            }


    }
}
</script>

<style>

</style>
