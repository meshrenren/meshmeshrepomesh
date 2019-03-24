<template>
	<div class = "loan-list">
		<div class="box box-info" v-loading = "pageLoading">
            <div class="box-header with-border">
              	<h3 class="box-title">View slfjbjldsbfjlfssLoans</h3>
            </div>
	        <div class = "box-body">
	        	<el-row :gutter = "20">
	        		<el-col :span="10">
	        			<el-input v-model="or_number" @keyup.enter.native ="searchPayment">
		                    <el-button slot="append" type = "default" @click="searchPayment()">Search</el-button>
		                </el-input>
		                <table class = "mt-10">
		                	<tr>
		                		<th>Name: </th>
		                		<td class = "pl-10" v-if = "paymentRecord">{{ paymentRecord.name }}</td>
		                		<th>Status: </th>
		                		<td class = "pl-10" v-if = "paymentRecord">
		                			<template v-if = "paymentRecord.posted_date">
		                				UNPOSTED
		                			</template>
		                			<template v-if = "paymentRecord.posted_date">
		                				POSTED
		                			</template>
		                		</td>
		                	</tr>
		                	<tr>
		                		<th>Date Transact: </th>
		                		<td class = "pl-10" v-if = "paymentRecord">{{ paymentRecord.date_transact }}</td>
		                	</tr>
		                </table>
	        			<div class="box mt-20">
		                    <div class="box-header">
		                        <h3 class="box-title">TOTAL</h3>
		                    </div>
		                    <div class="box-body payment-entry-list">
		                        <el-table
		                            :data="totalAccounts"
		                            border striped
		                            style="width: 100%"
		                            height = "400px">
		                            <el-table-column
		                                prop="productData.name"
		                                label="Account">                            
		                            </el-table-column>
		                            <el-table-column
		                                prop="type"
		                                label="Type"> 
		                            </el-table-column>
		                            <el-table-column
		                                prop="amount"
		                                label="Amount">
		                            </el-table-column>
		                        </el-table>
		                    </div>
		                </div>
	        		</el-col>
	        		<el-col :span="14">
						<div class="box">
		                    <div class="box-header">
		                        <h3 class="box-title">ALL ACCOUNTS</h3>
		                        <!-- <div class="box-tools pull-right">
		                            <el-button class = "auto-width pull-right ml-5" size = "small" type = "danger" @click = "cancelPayment()">CANCEL</el-button>
		                            <el-button class = "auto-width pull-right " size = "small" type = "primary" @click = "finishPayment()">SAVE</el-button>
		                        </div> -->
		                    </div>
		                    <div class="box-body payment-entry-list mt-5">
		                        <el-table
		                            :data="allTotalAccount.filter(data => !nameSearch || (data.member && data.member.fullname.toLowerCase().includes(nameSearch.toLowerCase())))"
		                            border striped
		                            style="width: 100%"
		                            height = "480px">
		                            <el-table-column prop = "member.fullname"> 
		                                <template slot="header" slot-scope="scope">
		                                    <el-input v-model="nameSearch" size="mini" placeholder="Search Member"/>
		                                </template>                       
		                            </el-table-column>
		                            <el-table-column
		                                prop="productData.name"
		                                label="Account">                            
		                            </el-table-column>
		                            <el-table-column
		                                prop="amount"
		                                label="Amount">
		                            </el-table-column>
		                            <el-table-column
		                                prop="type"
		                                label="Type"> 
		                            </el-table-column>
		                        </el-table>
		                    </div>
		                </div>
	        		</el-col>
	        	</el-row>
	        </div>
	    </div>
	</div>
</template>



<script>
	window.noty = require('noty');
    import axios from 'axios'
    import Noty from 'noty'
    import cloneDeep from 'lodash/cloneDeep'    
    import _forEach from 'lodash/forEach'

    import swalAlert from '../../mixins/swalAlert.js'

export default {
    mixins: [swalAlert],
	props: [],
	data: function () {
		return {
			or_number 				: "",
			pageLoading 			: false,
			allTotalAccount 	 	: [],
			paymentRecord 			: {},
			nameSearch 				: ''
		}
	},
	created(){
	},
    computed:{
        totalAccounts(){
            let list = this.allTotalAccount
            let account = []

            _forEach(list, rs =>{
                let acct = cloneDeep(rs)
                let getInd = -1
                if(acct.type == "OTHERS"){
                	getInd = account.findIndex(rs => { return rs.type == acct.type && rs.particular_id == acct.particular_id})
                }else{
                	getInd = account.findIndex(rs => { return rs.type == acct.type && rs.product_id == acct.product_id})
                }
                if(getInd >= 0){
                    let amt = cloneDeep(Number(account[getInd].amount)) + Number(acct.amount)
                    account[getInd].amount = amt
                }
                else{
                    account.push(acct)
                }
            })

            _forEach(account, rs =>{
                rs.amount = Number(rs.amount).toFixed(2)
            })

            return account 
        }
    },
	methods:{	

    	searchPayment(){
    		console.log()
    		this.pageLoading = true

            this.$API.Payment.getPaymentDetails(this.or_number)
            .then(result => {
                let res = result.data
                if(res.success){
                	this.paymentRecord = res.paymentRecord
                	this.allTotalAccount = res.paymentList
                }
                else{
                	this.getSwalAlert("error", "OR Number Not Found")
                }
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