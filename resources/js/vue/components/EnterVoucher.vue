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

import _message from '../mixins/messageDialog.js'
import {dialogComponent} from '../mixins/dialogComponent.js'

export default {
    props: {
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
    		particulars 	: [],
    		gvNumber 		: '',
    		pageLoading 	: false,
    		dialogVisible	: true,
            transactionDate : dt
    	}
    },
    mounted(){
    },
    methods:{
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
    		}
    		this.$emit('processentervoucher', params)
    		this.dialogVisible = false
    	},
    }
}
</script>

