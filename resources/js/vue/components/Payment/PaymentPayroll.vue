<template>
	<div class="payment-payroll" v-loading = "allLoading">
		<el-row :gutter="20">
            <el-col :span="17">
                <el-form :model="paymentModel" :rules="rulesPayment" ref="paymentForm" label-width="160px" class="demo-ruleForm" label-position = "top">
                    <el-row :gutter="20">
                    	<el-col :span="4">
                            <el-form-item label="OR Number" prop="or_num" ref="or_num">
                                <el-select
                                    @change = "changeOR"
                                    v-model="paymentModel.or_num"
                                    filterable
                                    remote
                                    reserve-keyword
                                    placeholder="Enter OR"
                                    :remote-method="remoteMethod"
                                    :loading="loading">
                                    <el-option
                                      v-for="item in recordList"
                                      :key="item.id"
                                      :label="item.or_num"
                                      :value="item.id">
                                    </el-option>
                                </el-select>
                            </el-form-item>
                        </el-col>
                        <el-col :span="6">
                            <el-form-item label=" ">
                                <el-input type="text" :disabled = "true" v-model="paymentModel.date_transact"></el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item label=" ">
                                <el-input type="text" :disabled = "true" v-model="paymentModel.name"></el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span="6">
                            <el-form-item label=" ">
                                <el-input type="text" :disabled = "true" v-model="totalAmount"></el-input>
                            </el-form-item>
                        </el-col>
                    </el-row>
                </el-form>
                <el-row :gutter="20">
                    <el-col :span="8">
	                	<el-table
	                        :data="selectedPayments"
	                        border striped
	                        style="width: 100%"
	                        height = "400px">
	                        <el-table-column
	                            prop="particular.name"
	                            label="Description">
	                        </el-table-column>
	                    </el-table>
	                </el-col>
                </el-row>
            </el-col>
        </el-row>
	</div>
</template>
<script>
	window.noty = require('noty');
    import axios from 'axios'
    import Noty from 'noty'  
    import cloneDeep from 'lodash/cloneDeep'    
    import _forEach from 'lodash/forEach'


export default {
    props: ['dataPaymentModel', 'dataPayrollModel', 'dataPayrollListModel'],
    data: function () {  
    	let formPayment  = {}
        this.dataPaymentModel.forEach(function(detail){
            formPayment[detail] = null
        })

        let formPayroll  = {}
        this.dataPayrollModel.forEach(function(detail){
            formPayment[detail] = null
        })

        let formPayrollList  = {}
        this.dataPayrollListModel.forEach(function(detail){
            formPayment[detail] = null
        })
    	return{
    		paymentModel 		: formPayment,
    		payrollModel 		: formPayroll,
    		payrollListModel 	: formPayrollList,
    		loading 			: false,
    		allLoading 			: false,
    		payrollRecordList	: {},
    		allrecordList 		: {},
    		recordList 			: {},
    		rulesPayment 		: {},
    		selectedPayments 	: [],
    		totalAmount 		: ''
    	}
    },
    created(){
    	this.getPayrollRecord()
    },
    methods:{
    	getPayrollRecord(){
    		this.$API.Payment.getPayrollRecord()
            .then(result => {
                this.payrollRecordList = result.data.data
                this.recordDistinct()
            })
            .catch(err => {
                console.log(err)
            })
            .then(_ => { 
                this.allLoading = false
            })
    	},
    	recordDistinct(){
    		let list = []
    		_forEach(this.payrollRecordList, pr =>{
    			let findRecord = list.find(rs => { return rs.or_num == pr.or_num})
    			if(!findRecord){
    				list.push(pr)
    			}

    		})
    		this.allrecordList = list
    	},
        remoteMethod(query){
            if (query && query !== '') {
                this.loading = true;
                setTimeout(() => {
                    this.loading = false;
                    this.recordList = this.allrecordList.filter(item => {
                        return item.or_num.toLowerCase().indexOf(query.toLowerCase()) > -1;
                    });
                }, 200);
            } else {
              this.recordList = [];
            }
        },
        changeOR(val){
        	console.log("val", val)
        	let findRecord = this.allrecordList.find(rs => { return rs.id == val})
        	console.log("findRecord", findRecord)
        	if(findRecord){
        		_forEach(this.paymentModel, (elem, ind)  =>{
	        		this.paymentModel[ind] = findRecord[ind]
	        	})


	        	let recordList = this.payrollRecordList.filter(rs => { return rs.or_num == findRecord.or_num})
	        	this.selectedPayments = recordList
	        	if(recordList.length > 0){
	        		let total = 0
	        		_forEach(recordList, rl  =>{
		        		total = parseFloat(total) + parseFloat(rl.amount_paid)
		        	})

		        	this.totalAmount = total
	        	}
        	}

        	
        	
        }
    }
}
</script>

<style lang="scss">
    
    @import '~noty/src/noty.scss';
    .payment-payroll{
        .el-form{
            .el-button{
                width: 100%;
            }
        }
        .el-form-item{
            .el-form-item__label{
                line-height: 10px;
                padding: 0 0 0px;
            }
        }

        .el-select{
            width: 100%;
        }

        .el-date-editor{
            width: 100%;
        }


        .el-button{
            width: 100%;
        }

        hr{
            border-top: 1px solid #d6d6d6;
            margin-top: 10px;
            margin-bottom: 10px;
        }
    }

	
</style>