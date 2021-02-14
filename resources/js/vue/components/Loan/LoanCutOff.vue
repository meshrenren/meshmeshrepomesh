<template>
	<div class = "loan-cut-off">
		<div class="box box-info" v-loading = "pageLoading">
            <div class="box-header with-border">
              	<h3 class="box-title">{{ title }}</h3>
            </div>
	        <div class = "box-body">
	        	<el-row :gutter = "20">
	        		<el-col :span = "12">
	        			<el-form :model="searchForm" :rules = "formRule" ref="searchForm" label-width="150px" class="demo-ruleForm" >
							<!-- <el-form-item label="Loan Product"  prop = "selectProduct" >
								<el-select v-model="searchForm.selectProduct"
									placeholder="Select"
									filterable>
									<el-option
                                        label="All"
                                        :value="null">
                                    </el-option>
									<el-option
                                        v-for="item in pageData.loanProducts"
                                        :key="parseInt(item.id)"
                                        :label="item.product_name"
                                        :value="parseInt(item.id)">
                                    </el-option>
								</el-select>
							</el-form-item>
 -->							<!-- <el-form-item label="Search"  prop = "searchName" >
								<el-input placeholder="Search name" v-model="searchForm.searchName">
								</el-input>
							</el-form-item> -->
						</el-form>
	        		</el-col>
	        		<el-col :span="24">
	        			<!-- <div class = "right-toolbar">
            				<el-button type = "default" @click = "printForm('print')">PRINT</el-button>            			
            			</div> -->
            			<div class = "right-toolbar">
            				<el-button type = "primary" @click = "confirmSave()">SAVE</el-button>            			
            			</div>

            			<el-table
							:data="loanAccounts"
				            border striped
				            style="width: 100%"
				            class = "mt-5"
				            max-height = "500px"
							width = "100%">

				            <el-table-column label="Name">      
				                <template slot-scope="scope"> 
				                	{{ scope.row.member ? scope.row.member.fullname : "" }} 
				                </template>                       
				            </el-table-column>

				            <el-table-column label="Principal" prop = "principal" >  
				                <template slot-scope="scope"> 
				                	{{ $nf.formatNumber(scope.row.principal, 2) }} 
				                </template>                      
				            </el-table-column>

				            <el-table-column label="Balance" prop = "principal_balance" >  
				                <template slot-scope="scope"> 
				                	{{ $nf.formatNumber(scope.row.principal_balance, 2) }} 
				                </template>                      
				            </el-table-column>

				            <el-table-column label="Cut PI" prop = "cutoff_pi" >  
				                <template slot-scope="scope"> 
				                	<el-input v-model="scope.row.cutoff_pi">
									</el-input>
				                	<!-- {{ $nf.formatNumber(scope.row.cutoff_pi, 2) }}  -->
				                </template>                      
				            </el-table-column>

				            <el-table-column label="Cut Interest" prop = "cutoff_int" >  
				                <template slot-scope="scope"> 
				                	<el-input v-model="scope.row.cutoff_int">
									</el-input>
				                	<!-- {{ $nf.formatNumber(scope.row.cutoff_int, 2) }}  -->
				                </template>                      
				            </el-table-column>

				        </el-table>
	        		</el-col>
	        	</el-row>
	        </div>
	    </div>
	</div>
</template>


<script>
window.noty = require('noty')
import Noty from 'noty'
import cloneDeep from 'lodash/cloneDeep'    
import _forEach from 'lodash/forEach'
import _sortBy from 'lodash/sortBy'

import swalAlert from '../../mixins/swalAlert.js'
import _message from '../../mixins/messageDialog.js'
import fileExport from '../../mixins/fileExport'

export default {
	props:{
        pageData : {
            type : Object,
            require : true
        },
    },
    mixins: [swalAlert, _message, fileExport],
	data: function () {
		let searchForm = { selectProduct : null, searchName : null}
		return {
			searchForm 		: searchForm,
			formRule 		: {},
			pageLoading 	: false,
			loanAccounts 	: this.pageData.accountList,
			currMoment 		: moment(this.$systemDate),
			title 			: this.pageData.type == 'RL' ? 'Regular Loan Cut Off' : 'Appliance Loan Cut Off',
		}
	},
	created(){
		this.getLoanArrears()
	},
    computed:{
    },  
	methods:{	
		getSummaries(param) {
	        const { columns, data } = param;
	        const sums = [];
	        columns.forEach((column, index) => {
	          	if (index === 0) {
		            sums[index] = 'TOTAL';
		            return;
	          	}
	          	const values = data.map(item => Number(item[column.property]));
	         	if (!values.every(value => isNaN(value))) {
		            sums[index] = values.reduce((prev, curr) => {
		              const value = Number(curr);
		              if (!isNaN(value)) {
		                return prev + curr;
		              } else {
		                return prev;
		              }
		            }, 0);
		            sums[index] = this.$nf.formatNumber(sums[index], 2)
	          	} else {
	            	sums[index] = '';
	          	}
        	});

        	return sums;
      	},
      	processCutOff(){
      		let cutOffs = []
      		_forEach(this.loanAccounts, ln =>{
      			let arr = {loan_id : ln.loan_id, 
      				member_id : ln.member_id, 
      				prepaid_interest : ln.cutoff_pi,
      				interest_earned : ln.cutoff_int,
      				prepaid_interest_cutoff : ln.cutoff_pi_orig,
      				interest_earned_cutoff : ln.cutoff_int_orig }

      			cutOffs.push(arr)
      		})

      		return cutOffs
      	},
      	confirmSave(){
      		this.$swal({
              title: 'Run Cut Off?',
              text: "Are you sure you want to save cut-off data?",
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
            		this.saveCutOff()
            	}
            })
      	},
      	saveCutOff(){

      		this.pageLoading = true
      		let cutOffsData = this.processCutOff()
            this.$API.Loan.saveCutoff({accounts : cutOffsData})
            .then(result => {
                let res = result.data
                if(res.success){
                	location.reload()
                }
                else{
                	new Noty({
	                    theme: 'relax',
	                    type: "error",
	                    layout: 'topRight',
	                    text: "An error occured. Please contact administrator.",
	                    timeout: 3000
	                }).show();
                }
            })
            .catch(err => {
                console.log(err)
            })
            .then(_ => { 
                this.pageLoading = false
            })
      	},
		getLoanArrears(){
			let form = cloneDeep(this.searchForm)

			console.log('Test', form)
			this.pageLoading = true

            this.$API.Report.getLoanArrears(form)
            .then(result => {
                let res = result.data
                this.arrearsList = res.data
            })
            .catch(err => {
                console.log(err)
            })
            .then(_ => { 
                this.pageLoading = false
            })
			
		},
    }
}

</script>
<style lang="scss">
.report-loan-arrears{

	.sub-total{
		color: #0483ce;
		font-weight: bold;
	}

	.grand-total{
		color: #e43939;
		font-weight: bold;
	}

	.el-form-item{
		margin-bottom: 0px;
	}
}
</style>