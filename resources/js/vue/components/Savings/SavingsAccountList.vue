<template>
	<div class="savings-deposit-form" v-loading = "pageLoading">
		<div class="box box-info">
            <div class="box-header with-border">
              	<h3 class="box-title">View Savings Account</h3>
            </div>
            <div class = "box-body">
            	<el-row :gutter="20">
            		<el-col :span="10">
            			<el-input class = "mb-10" v-model="search" size="mini" placeholder="Search account name"/>
						<el-table 
							:data="savingsList.filter(data => !search || (data.account_name && data.account_name.toLowerCase().includes(search.toLowerCase())) || (data.member && data.member.fullname.toLowerCase().includes(search.toLowerCase())))"
							style="width: 100%" 
							height="450px" stripe border 
							v-loading = "loadingTable">
				            <el-table-column label="ID" width="100">
				                <template slot-scope="scope">
				                    <span style="margin-left: 10px">{{ scope.row.account_no }}</span>
				                </template>
				            </el-table-column>
				            <el-table-column label="Name"  width="230">
				                <template slot-scope="scope">
				                   	<span v-if = "scope.row.account_name">{{ scope.row.account_name }}</span>
				                   	<span v-else-if = "scope.row.member">{{ scope.row.member.fullname }}</span>
				                </template>
				            </el-table-column>
				            <el-table-column label="Balance">
				                <template slot-scope="scope">
				                   	<span >{{ $nf.formatNumber(scope.row.balance, 2) }}</span>
				                </template>
				            </el-table-column>
				            <el-table-column label="Action">
				                <template slot-scope="scope">
				                    <el-button size="mini" @click="selectAccount(scope.index, scope.row)">Select</el-button>
				                </template>
				            </el-table-column>
				        </el-table>
            		</el-col>
            		<el-col :span="14">
            			<el-row :gutter="20">
            				<el-col :span="16">
		            			<table>
		            				<tr>
		            					<th>Account Name: </th> 
		            					<td v-if = "accountSelected" class = "pl-10">
		            						<template v-if = "accountSelected.account_name ">
		            							<span>{{ accountSelected.account_name }} </span>
		            						</template>
		            						<template v-else-if = "accountSelected.member ">
		            							<span>{{ accountSelected.member.fullname }} </span>
		            						</template>
		            					</td>
		            				</tr>
		            				<tr>
		            					<th>Account Number: </th> 
		            					<td v-if = "accountSelected" class = "pl-10">{{ accountSelected.account_no }} </td>
		            				</tr>
		            				<tr>
		            					<th>Balance: </th> 
		            					<td v-if = "accountSelected" class = "pl-10">{{ $nf.formatNumber(accountSelected.balance) }} </td>
		            				</tr>
		            			</table>
		            		</el-col>

            				<el-col :span="8"> 
		            			<div class = "right-toolbar">
		            				<!-- <button class = "btn btn-app" @click = "printForm('pdf')"><i class = "fa fa-file-pdf-0"></i> Print Ledger</button> -->
		            				<el-button type = "default" @click = "printForm('print')">Print Ledger</el-button>
		            				<!-- <button class = "btn btn-app" @click = "printForm('print')"><i class = "fa fa-print"></i> Print</button> -->
		            			</div>
		            		</el-col>
		            	</el-row>
						<el-table :data="transactionList" height = "430px" stripe border v-loading = "loadingTransTable">
				            <el-table-column label="Date Transact">
				                <template slot-scope="scope">
				                    <span>{{ $df.formatDate(scope.row.transaction_date, "YYYY-MM-DD") }}</span>
				                </template>
				            </el-table-column>
				            <el-table-column label="In">
				                <template slot-scope="scope">
				                    <span>{{ $nf.formatNumber(scope.row.amount_in) }}</span>
				                </template>
				            </el-table-column>
				            <el-table-column label="Out">
				                <template slot-scope="scope">
				                    <span>{{ $nf.formatNumber(scope.row.amount_out) }}</span>
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
				                    <span>{{ $nf.formatNumber(scope.row.running_balance, 2) }}</span>
				                </template>
				            </el-table-column>
				            <el-table-column label="Remarks">
				                <template slot-scope="scope">
				                    <span>{{ scope.row.remarks }}</span>
				                </template>
				            </el-table-column>
				        </el-table>
            		</el-col>
            	</el-row>
            </div>
        </div>
        <div v-html = "samplePrint"></div>
    </div>
</template>
<script> 
	window.noty = require('noty')
    import Noty from 'noty'
    import cloneDeep from 'lodash/cloneDeep'  
    import _forEach from 'lodash/forEach'  
    import SearchSavingsAccount from '../General/SearchSavingsAccount.vue' 

	import fileExport from '../../mixins/fileExport'

export default {
	mixins: [fileExport],
	props: ['baseUrl'],
	data: function () {
		return{
			pageLoading 			: false,
			savingsList				: [],
			loadingTable 			: false,
			search 					: "",
			loadingTransTable 		: false,
			accountTransactionList 	: [],
			accountSelected 		: {},
			samplePrint 			: ""
		}
	},
	created(){
		this.getAccountList()
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
		}
	},
	methods:{
		getAccountList(){
			this.loadingTable = true

            this.$API.Savings.getAccounts()
            .then(result => {
                let res = result.data
                this.savingsList = res
            })
            .catch(err => {
                console.log(err)
            })
            .then(_ => { 
                this.loadingTable = false
            })
		},
		selectAccount(index, data){
			this.accountSelected = cloneDeep(data)
			this.getTransaction(this.accountSelected.account_no)
		},
    	getTransaction(account_no){
    		this.loadingTransTable = true

            this.$API.Savings.getTransaction(account_no)
            .then(result => {
                var res = result.data
                if(res.length > 0 ){
                    this.accountTransactionList = res
                }
            })
            .catch(err => {
                console.log(err)
            })
            .then(_ => { 
                this.loadingTransTable = false
            })
    	},
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
    		let accName = account.account_name
    		if(account.member){
    			accName = account.member.fullname
    		}

    		let data = {
    			account_no : account.account_no,
    			account_name : accName,
    			balance : account.balance,
    			transaction : this.transactionList
    		}

			this.$API.General.printList(data, type, 'Savings')
			.then(result => {
				let res = result.data
				if(type == 'pdf'){
					this.exporter(type, 'Savings Deposit Transaction', res)
				}
				else if(type == 'print'){
					this.winPrint(res.data, 'Savings Deposit Transaction')
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