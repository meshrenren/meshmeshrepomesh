<template>
	<el-dialog @close="closeModal" :visible.sync="dialogVisible" width = "40%" top = "20px" >
	  	<span slot="title" class="el-dialog__title" >General Voucher</span>
		<div class="voucher-view-form" v-loading = "pageLoading">
			<el-row :gutter = "5">
				<el-col :span = "12">
					<label>GV Number</label>
					<el-input v-model="gvNumber" :required = "gvRequired"></el-input>
				</el-col>
                <el-col :span = "12">
                    <label>Transaction Date</label>
                    <el-date-picker v-model="transactionDate" type="date" placeholder="Pick a date"> </el-date-picker>
                </el-col>
			</el-row>
			<el-table
		      	:data="voucherDataList"
		      	style="width: 100%"
                :summary-method="getSummaries"
                show-summary
		      	>
			    <el-table-column
			        prop="debit"
			        label="DEBIT"
                    width="100px">
			        <template slot-scope="scope">
	                    <span>{{ $nf.formatNumber(scope.row.debit) }}</span>
	                </template>
			    </el-table-column>
			    </el-table-column>
			    <el-table-column
			        prop="particular_name"
			        label="Particular"
                    align="center">
			    </el-table-column>
			    <el-table-column
			        prop="credit"
			        label="CREDIT"
			        width="100px">
			        <template slot-scope="scope">
	                    <span>{{ $nf.formatNumber(scope.row.credit) }}</span>
	                </template>
	            </el-table-column>
			</el-table>
		</div>
		<div slot="footer" class="dialog-footer">
	    	<el-button type = "primary" @click = "processAccount()">Process</el-button>
	  	</div>
	</el-dialog> 
</template>
<script>

window.noty = require('noty')
import Noty from 'noty'  
import cloneDeep from 'lodash/cloneDeep'    
import _forEach from 'lodash/forEach'
import _uniq from 'lodash/uniq'

import _message from '../../mixins/messageDialog.js'
import {dialogComponent} from '../../mixins/dialogComponent.js'

export default {
    props: {
    	/*
    	Format of array: {particular_name: "", amount: 0, type : "CREDIT/DEBIT" }
    	*/
    	dataList:{
    		type: Array,
    		required: true
    	},
        gvRequired:{
            type: Boolean,
            required: false,
            default: false
        },
        dateTransact:{},
    },
    mixins: [dialogComponent, _message],
    data(){
        let dt = this.dateTransact ? this.dateTransact : moment(this.$systemDate)
    	return{
    		voucherData 	: this.dataList,
    		particulars 	: [],
    		gvNumber 		: '',
    		pageLoading 	: false,
    		dialogVisible	: true,
            transactionDate : dt
    	}
    },
    mounted(){
        if(this.voucherData.length > 0){
            this.getParticulars()
        }
    },
    computed:{
    	voucherDataList(){
    		let list = []
    		let particularsList = this.particulars

    		_forEach(particularsList, pt =>{
    			let arr = {
                    type : pt.category,
                    account_no : null,
    				category_type : null,
    				category_id : null,
    				credit : 0,
    				debit : 0,
    				particular_id : pt.id,
    				particular_name : pt.name,
    			}
                let acct_no = null
    			_forEach(this.voucherData, vd=>{
                    let hasParticular = false
    				if(vd.particular_name && pt.name.toLowerCase() == vd.particular_name.toLowerCase()){
                        hasParticular = true
    				}
                    else if(vd.particular_id && Number(vd.particular_id) == Number(pt.id)){
                        hasParticular = true
                    }

                    if(hasParticular){
                        if(vd.type == "CREDIT"){
                            arr.credit = Number(arr.credit) + Number(vd.amount)
                        }
                        else if(vd.type == "DEBIT"){
                            arr.debit = Number(arr.debit) + Number(vd.amount)
                        }
                        if(vd.account_no){
                            acct_no = vd.account_no
                        }
                    }
	    		})

                arr.account_no = acct_no

	    		list.push(arr)
    		})
    		
    		return list
    	},
    },
    methods:{
    	mapParticulars(){
            let list = []
            let ids = []
            _forEach(this.voucherData, vd =>{
                if(vd.particular_name){ 
                    list.push(vd.particular_name) 
                }
                else if(vd.particular_id){
                    ids.push(vd.particular_id) 
                }
            })

	    	list = _uniq(list)
            ids = _uniq(ids)

	    	return {names : list, ids : ids}
    	},

    	getParticulars(){
    		this.pageLoading = true
    		let mapParticulars = this.mapParticulars() 

            let params = {
                filter : mapParticulars
            }

        	this.$API.General.getParticularsVoucher(params)
            .then(result => {
            	let res = result.data
                this.particulars = res.data
            })
            .catch(err => {
                console.log(err)
            })
            .then(_ => {
                this.pageLoading = false
            })

        },
    	processAccount(){
            console.log('processAccount')
    		if(!this.gvNumber){
                new Noty({
                    type: 'error',
                    layout: 'topRight',
                    text: 'Please enter GV ',
                    timeout: 2500
                }).show()

                console.log('here')
    			return
    		}

            this.$swal({
                title: 'Process Voucher',
                text: "Are you sure you want to process vouher? This will automatically be posted.",
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: '#d33',
                confirmButtonText: 'Proceed',
                focusConfirm: false,
                focusCancel: true,
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                customClass: {
                    container: 'swal-voucher'
                }
            }).then(result => {
                if (result.value) {
                    let params = {
                        gv_num  : this.gvNumber,
                        transaction_date  : this.$df.formatDate(this.transactionDate, "YYYY-MM-DD") ,
                        voucher_entries : this.voucherDataList
                    }
                    this.$emit('processvoucher', params)
                    this.dialogVisible = false
                }
            })
        		
    	},
        getSummaries(param){
            const { columns, data } = param;
            const sums = [];

            columns.forEach((column, index) => {
                if (index === 1) {
                    sums[index] = 'TOTAL';
                    return;
                }
                else if(index === 0 || index === 2){
                    const values = data.map(item => Number(item[column.property]));
                    if (!values.every(value => isNaN(value))) {
                        let sumAmount = values.reduce((prev, curr) => {
                            const value = Number(curr);
                            if (!isNaN(value)) {
                                return prev + curr;
                            } else {
                                return prev;
                            }
                        }, 0);
                        sums[index] = ' ' + this.$nf.formatNumber(sumAmount, 2)
                    } else {
                        sums[index] = 'N/A';
                    }
                }
                else{
                    sums[index] = '';
                    return;
                }

                
            });

            return sums;
        }
    }
}
</script>
<style type="scss">
    .swal-voucher{
        z-index : 10000;
    }
</style>

