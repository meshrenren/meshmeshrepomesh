<template>
	<div class = "view-particular">
		<div class="box box-info" v-loading = "pageLoading">
            <div class="box-header with-border">
              	<h3 class="box-title">View Particular Payments</h3>
            </div>
	        <div class = "box-body">
	        	<el-row :gutter = "20">
	        		<el-col :span = "6">
	        			<el-form :model="searchForm" :rules = "formRule" ref="searchForm" label-width="100px" class="demo-ruleForm" label-position = "top">
	        				
							<el-form-item label="View By"  prop = "viewBy" >
								<el-select v-model="searchForm.viewBy"
									placeholder="Select"
									filterable>
									<el-option label="View By Member" value='member'></el-option>
									<el-option label="View By Particular" value='particular'></el-option>
								</el-select>
							</el-form-item>
							<el-form-item label="Member" prop = "member_id" v-if = "searchForm.viewBy == 'member'">
								<el-select
									v-model="searchForm.member_id"
									filterable>
									<el-option
										v-for="(item, index) in memberList"
										:key="item.id"
										:value="Number(item.id)"
										:label="item.fullname"
										placeholder="Select">
									</el-option> 
								</el-select>
							</el-form-item>
							<el-form-item label="Particular"  prop = "particular_id">
								<el-select v-model="searchForm.particular_id"
									filterable>
									<el-option
                                        v-for="item in particularList"
                                        :key="parseInt(item.id)"
                                        :label="item.name"
                                        :value="parseInt(item.id)">
                                    </el-option>
								</el-select>
							</el-form-item>
							<el-form-item label="Period">
								<el-row :gutter="20">
									<el-col :span="12">
										<el-date-picker
											v-model="searchForm.filterDate[0]"
											type="date"
											:clearable="false"
											placeholder="Start">
										</el-date-picker>
									</el-col>

									<el-col :span="12">
										<el-date-picker
											v-model="searchForm.filterDate[1]"
											type="date"
											:clearable="false"
											placeholder="End">
										</el-date-picker>
									</el-col>
								</el-row>
							</el-form-item>

							<el-button class = "auto-width pull-right" size = "mini"  type = "primary" @click = "getPayments">Filter</el-button>
						</el-form>
	        		</el-col>
	        		<el-col :span="18">
	        			<div class = "right-toolbar">
            				<el-button type = "default" @click = "printForm('print')">PRINT</el-button>            			
            			</div>
	        			<el-table
							:data="paymentList"
				            border striped
				            style="width: 100%"
				            class = "mt-10"
				            max-height = "500px"
				    		show-summary >
				            <el-table-column label="Name" fixed>      
				                <template slot-scope="scope"> 
				                	{{ scope.row.member ? scope.row.member.fullname : scope.row.payment_name }} 
				                </template>                       
				            </el-table-column>

				            <el-table-column label="Particular" >  
				                <template slot-scope="scope"> 
				                	{{ scope.row.particular.name }} 
				                </template>                      
				            </el-table-column>

				            <el-table-column prop="or_num" label="OR Number"  >                      
				            </el-table-column>

				            <el-table-column label="Date" >  
				                <template slot-scope="scope"> 
				                	{{ scope.row.date_transact }} 
				                </template>                      
				            </el-table-column>

				            <el-table-column label="Amount" >  
				                <template slot-scope="scope"> 
				                	{{ $nf.formatNumber(scope.row.amount, 2) }} 
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
		let searchForm = { viewBy : 'member', filterDate : [null, null], member_id : null, particular_id : null}
		return {
			searchForm 		: searchForm,
			formRule 		: {},
			pageLoading 	: false,
			memberList 		: this.pageData.memberList,
			particularList 	: this.pageData.particularList,
			paymentList 	: []
		}
	},
	created(){
		var validateMember = (rule, value, callback) => {
    		if(this.searchForm.viewBy == 'member' && !value){
    			callback(new Error('Please select member.'));
    		}
    		callback();
	    };

		this.formRule = {
  			viewBy : [{ required: true, message: 'Please select data to view.', trigger: 'change' },],
  			particular_id : [{ required: true, message: 'Please select particular.', trigger: 'change' }],
  			member_id : [{ validator: validateMember, trigger: 'blur', trigger: 'change' }]
		}
	},
    computed:{
        totalAmount(){
        	let amount = 0
        	_forEach(this.paymentList, pn =>{
        		amount += parseFloat(pn.amount)
        	})

        	return amount
        }
    },  
	methods:{	
		getPayments(){
			let form = cloneDeep(this.searchForm)
			if(form.filterDate[0] == null || form.filterDate[1] == null){
				this.showMessage('error', 'Please enter period date', 5000)
				return
			}
			form.date = { start : this.$df.formatDate(form.filterDate[0], "YYYY-MM-DD"), end :  this.$df.formatDate(form.filterDate[1], "YYYY-MM-DD") }
			if(form.viewBy == 'particular'){
				form.member_id = null
			}
			this.$refs.searchForm.validate(valid => {
    			if (valid) {
    				this.pageLoading = true


		            this.$API.Payment.getPaymentParticular(form)
		            .then(result => {
		                let res = result.data
		                this.paymentList = res.data
		            })
		            .catch(err => {
		                console.log(err)
		            })
		            .then(_ => { 
		                this.pageLoading = false
		            })
    			}
    			else{

    			}
    		})

			
		},
		printForm(type){
    		if(this.paymentList.length == 0){
    			this.showMessage('error', 'No data to print.', 3000)
                return
    		}
    		this.pageLoading = true
    		let mem = this.memberList.find(fn => { return fn.id == this.searchForm.member_id })
    		let part = this.particularList.find(fn => { return fn.id == this.searchForm.particular_id })
    		let date = { start : this.$df.formatDate(this.searchForm.filterDate[0], "YYYY-MM-DD"), end :  this.$df.formatDate(this.searchForm.filterDate[1], "YYYY-MM-DD") }

    		let data = {
    			member : mem ? mem.fullname : null,
    			particular : part ? part.name : "",
    			period : date,
    			total_amount : this.totalAmount,
    			transaction : this.paymentList
    		}

			this.$API.General.printList(data, type, 'PaymentParticular')
			.then(result => {
				let res = result.data
				if(type == 'pdf'){
					this.exporter(type, 'Particular Payments', res)
				}
				else if(type == 'print'){
					this.winPrint(res.data, 'Particular Payments')
				}
			})
			.catch(err => { console.log(err)})
			.then(_ => { this.pageLoading = false })

    		//window.location.href = this.$baseUrl+"/savings/print-withdraw?account_no="+this.accountDetails.account_no;
    	}
    }
}

</script>
<style lang="scss">
.view-particular{
	.el-form-item{
		margin-bottom: 5px;

		.el-form-item__label{
			line-height: 0px;
   			padding: 0 0 0px;
    		margin-bottom: 0px;
		}

		.el-date-editor.el-input{
			width: auto;
		}

		.el-select{
			width: 100%;
		}

		.el-form-item__error{
			position: inherit;
		}
	}
}
</style>