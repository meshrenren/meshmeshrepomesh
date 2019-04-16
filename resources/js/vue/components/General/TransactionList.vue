<template>
	<div class="transaction-list" v-loading = "pageLoading">
		<el-row :gutter="20">
			<el-col :span="16">
    			<table>
    				<tr >
    					<th>Account Name: </th> 
    					<td v-if = "accountSelected" class = "pl-10">
    						<template v-if = "accountSelected.account_name">
    							<span>{{ accountSelected.account_name }} </span>
    						</template>
    						<template v-else-if = "accountSelected.member ">
    							<span>{{ accountSelected.member.fullname }} </span>
    						</template>
    					</td>
    				</tr>
    				<tr>
    					<th>Account Number: </th> 
    					<td v-if = "accountSelected && accountSelected.account_no" class = "pl-10">{{ accountSelected.account_no }} </td>
                        <td v-else-if = "accountSelected && accountSelected.accountnumber" class = "pl-10">{{ accountSelected.accountnumber }} </td>

                        
    				</tr>
    				<tr>
    					<th>Balance: </th> 
    					<td v-if = "accountSelected" class = "pl-10">{{ accountSelected.balance }} </td>
    				</tr>
    			</table>
    		</el-col>

			<el-col :span="8"> 
    			<div class = "right-toolbar">
    				<button class = "btn btn-app" @click = "printForm('pdf')"><i class="fa fa-file-pdf-o"></i> Export</button>
    				<!-- <button class = "btn btn-app" @click = "printForm('pdf')"><i class = "fa fa-print"></i> Print</button> -->
    			</div>
    		</el-col>
    	</el-row>
		<el-table :data="transactionList" height = "430px" stripe border>
            <el-table-column label="Date Transact">
                <template slot-scope="scope">
                    <span>{{ $df.formatDate(scope.row.transaction_date, "YYYY-MM-DD") }}</span>
                </template>
            </el-table-column>
            <el-table-column label="In">
                <template slot-scope="scope">
                    <span>{{ scope.row.amount_in }}</span>
                </template>
            </el-table-column>
            <el-table-column label="Out">
                <template slot-scope="scope">
                    <span>{{ scope.row.amount_out }}</span>
                </template>
            </el-table-column>
            <el-table-column label="Transaction Type">
                <template slot-scope="scope">
                    <span>{{ scope.row.transaction_type }}</span>
                </template>
            </el-table-column>
            <el-table-column label="Reference No">
                <template slot-scope="scope">
                    <span>{{ scope.row.ref_no }}</span>
                </template>
            </el-table-column>
            <el-table-column label="Running Balance">
                <template slot-scope="scope">
                    <span>{{ scope.row.running_balance }}</span>
                </template>
            </el-table-column>
            <el-table-column label="Remarks">
                <template slot-scope="scope">
                    <span>{{ scope.row.remarks }}</span>
                </template>
            </el-table-column>
        </el-table>
	</el-col>
        <div v-html = "samplePrint"></div>
    </div>
</template>
<script> 
	window.noty = require('noty');
    import axios from 'axios'
    import Noty from 'noty'
    import cloneDeep from 'lodash/cloneDeep'  
    import _forEach from 'lodash/forEach' 

	import fileExport from '../../mixins/fileExport'

export default {
	mixins: [fileExport],
	props: ['accountSelected', 'accountTransactionList', "accountType"],
	data: function () {
		return{
			pageLoading 			: false,
			search 					: "",
			loadingTransTable 		: false,
			samplePrint 			: ""
		}
	},
	created(){
		
	},
	computed:{
		transactionList(){
			let list = cloneDeep(this.accountTransactionList)

			_forEach(list, ls =>{
				ls['amount_out'] = ''
				ls['amount_in'] = ''
				if(ls.transaction_type == 'WITHDRWL')
					ls.amount_out = cloneDeep(ls.amount)
				else
					ls.amount_in = cloneDeep(ls.amount)
			})

			return list
		},
	},
	methods:{
		printForm(type){
    		if(this.transactionList.length == 0){
    			new Noty({
                    theme: 'relax',
                    type: "error",
                    layout: 'topRight',
                    text: "No transaction to process.",
                    timeout: 3000
                }).show();
                return
    		}
    		console.log("Here")
    		this.pageLoading = true
    		let account = cloneDeep(this.accountSelected)
    		let accName = ""
    		if(account.account_name)
    			accName = account.account_name
    		else if(account.member)
    			accName = account.member.fullname

    		let accountNumber = ""
    		if(this.accountType == 'Savings'){
    			accountNumber = account.account_no
    		}
    		else if(this.accountType == 'Share'){
    			accountNumber = account.accountnumber
    		}

    		let data = {
    			account_no : accountNumber,
    			account_name : accName,
    			balance : account.balance,
    			transaction : this.transactionList
    		}

			this.$API.General.printList(data, type, this.accountType)
			.then(result => {
				let res = result.data
				if(type == 'pdf'){
					this.exporter(type, 'Transaction List', res)
				}
				else if(type == 'print'){
					this.winPrint(res.data, 'Transaction List')
				}
			})
			.catch(err => { console.log(err)})
			.then(_ => { this.pageLoading = false })

    		//window.location.href = this.$baseUrl+"/savings/print-withdraw?account_no="+this.accountDetails.account_no;
    	},
	}
}
</script>

<style lang="scss">
  	@import '../../assets/site.scss';
  	@import '~noty/src/noty.scss';

  	.right-toolbar{
  		text-align: right;
  		i{
  			font-size: 14px;
  		}
  	}
</style>