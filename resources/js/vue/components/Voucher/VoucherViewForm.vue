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
		      	>
			    <el-table-column
			        prop="balance"
			        label="DEBIT">
			        <template slot-scope="scope">
	                    <span>{{ $nf.formatNumber(scope.row.debit) }}</span>
	                </template>
			    </el-table-column>
			    </el-table-column>
			    <el-table-column
			        prop="particular_name"
			        label="Particular">
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
    	this.getParticulars()
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
    				if(pt.name.toLowerCase() == vd.particular_name.toLowerCase()){
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
    		let list = this.voucherData.map(em => {
	    		return em.particular_name
	    	})

	    	list = _uniq(list)

	    	return list
    	},

    	getParticulars(){
    		this.pageLoading = true
    		let names = this.mapParticulars() 

        	this.$API.General.getParticularsByName(names)
            .then(result => {
            	let res = result.data
                console.log("result", result)
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

    		let params = {
                gv_num  : this.gvNumber,
                transaction_date  : this.$df.formatDate(this.transactionDate, "YYYY-MM-DD") ,
    			voucher_entries : this.voucherDataList
    		}
    		this.$emit('processvoucher', params)
    		this.dialogVisible = false
    	},
    }
}
</script>

