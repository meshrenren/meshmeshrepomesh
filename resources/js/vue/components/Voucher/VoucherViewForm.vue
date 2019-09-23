<template>
	<el-dialog @close="closeModal" :visible.sync="dialogVisible" width = "40%" top = "20px" >
	  	<span slot="title" class="el-dialog__title" >General Voucher</span>
		<div class="voucher-view-form" v-loading = "pageLoading">
			<el-row :gutter = "5">
				<el-col :span = "24">
					<label>GV Number</label>
					<el-input v-model="gvNumber"></el-input>
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
    	}
    },
    mixins: [dialogComponent, _message],
    data(){
    	return{
    		voucherData 	: this.dataList,
    		particulars 	: [],
    		gvNumber 		: '',
    		pageLoading 	: false,
    		dialogVisible	: true,
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
    				category_type : null,
    				category_id : null,
    				credit : 0,
    				debit : 0,
    				particular_id : pt.id,
    				particular_name : pt.name,
    			}

    			_forEach(this.voucherData, vd=>{
    				if(pt.name == vd.particular_name){
    					if(vd.type == "CREDIT"){
    						arr.credit = Number(arr.credit) + Number(vd.amount)
    					}
    					else if(vd.type == "DEBIT"){
    						arr.debit = Number(arr.debit) + Number(vd.amount)
    					}

    				}
	    		})

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
    		if(!this.gvNumber){
    			this.showMessage('error', 'Please enter GV Number')
    			return
    		}
    		let params = {
    			gv_num 	: this.gvNumber,
    			voucher_entries : this.voucherDataList
    		}
    		this.$emit('processvoucher', params)
    		this.dialogVisible = false
    	},
    }
}
</script>

