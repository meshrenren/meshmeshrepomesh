<template>
	<div class = "loan-list">
		<div class="box box-info" v-loading = "pageLoading">
            <div class="box-header with-border">
              	<h3 class="box-title">View Loans</h3>
            </div>
	        <div class = "box-body">
	        	<el-row :gutter = "20">
	        		<el-col :span="12">
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
							<div class = "Loan List">
			        			<h4>Member's List of Loan</h4>
								<el-table :data="accountLoanList"height = "350px" stripe border>
						            <el-table-column label="Date Transact">
						                <template slot-scope="scope">
						                    <span>{{ scope.row.release_date }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Type" width = "200px">
						                <template slot-scope="scope">
						                    <span>{{ scope.row.product.product_name }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Principal Loan">
						                <template slot-scope="scope">
						                    <span>{{ scope.row.principal }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Balance">
						                <template slot-scope="scope">
						                    <span>{{ scope.row.principal_balance  }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Duration">
						                <template slot-scope="scope">
						                    <span>{{ scope.row.term }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Action">
						                <template slot-scope="scope">
						                    <el-button size="mini" @click="selectAccount(scope.index, scope.row)">Select</el-button>
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
			       	<el-col :span = "12">
			       		<div class = "Loan List">
		        			<h4>Transactions</h4>
		        			<label>Loan Account : </label> <span v-if = "selectedAccount && selectedAccount.product"> {{ selectedAccount.product.product_name}}</span>
							<el-table :data="loanTransaction"height = "450px" stripe border>
					            <el-table-column label="Date Transaction">
					                <template slot-scope="scope">
					                    <span>{{ scope.row.transaction_date }}</span>
					                </template>
					            </el-table-column>
					            <el-table-column label="Refernce No">
					                <template slot-scope="scope">
					                    <span>{{ scope.row.product.OR_no }}</span>
					                </template>
					            </el-table-column>
					            <el-table-column label="Principal Paid">
					                <template slot-scope="scope">
					                    <span>{{ scope.row.principal_paid }}</span>
					                </template>
					            </el-table-column>
					            <el-table-column label="Prepaid Interest Paid">
					                <template slot-scope="scope">
					                    <span>{{ scope.row.prepaid_intpaid }}</span>
					                </template>
					            </el-table-column>
					            <el-table-column label="Interest">
					                <template slot-scope="scope">
					                    <span>{{ scope.row.interest_earned }}</span>
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
	window.noty = require('noty');
    import axios from 'axios'
    import Noty from 'noty'
    import cloneDeep from 'lodash/cloneDeep'    
    import _forEach from 'lodash/forEach'

export default {
	props: [],
	data: function () {
		return {
			memberDetails			: {id : null, fullname : null, station : {}},
			showSearchModal			: false,
			accountLoanList 		: [],
			pageLoading 			: false,
			loanTransaction 		: [],
			selectedAccount 		: {}
		}
	},
	created(){
	},
	methods:{		
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
	}
</style>