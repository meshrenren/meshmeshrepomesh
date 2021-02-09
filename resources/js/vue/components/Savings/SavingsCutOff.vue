<template>
	<div class="savings-deposit-form" v-loading = "pageLoading">
		<div class="box box-info">
            <div class="box-header with-border">
              	<h3 class="box-title">Savings Account Cut Off</h3>
            </div>
            <div class = "box-body">
            	<el-row :gutter="20">
            		<el-col :span="20">
            			<el-input class = "mb-10" v-model="search" size="mini" placeholder="Search account name"/>
						<el-table 
							:data="savingsList.filter(data => !search || (data.account_name && data.account_name.toLowerCase().includes(search.toLowerCase())) || (data.member && data.member.fullname.toLowerCase().includes(search.toLowerCase())))"
							style="width: 100%" 
							height="450px" stripe border 
							v-loading = "loadingTable"
							show-summary 
							width = "100%"
							:summary-method="getSummaries">
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
				            <el-table-column label="Balance" prop = "balance">
				                <template slot-scope="scope">
				                   	<span >{{ $nf.formatNumber(scope.row.balance, 2) }}</span>
				                </template>
				            </el-table-column>
				            <el-table-column label="Interest" prop = "total_interest">
				                <template slot-scope="scope">
				                   	<span >{{ $nf.formatNumber(scope.row.total_interest, 2) }}</span>
				                </template>
				            </el-table-column>
				            <!-- <el-table-column label="Action">
				                <template slot-scope="scope">
				                    <el-button size="mini" @click="selectAccount(scope.index, scope.row)">Select</el-button>
				                </template>
				            </el-table-column> -->
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

export default {
	props: ['dataTransaction'],
	data: function () {
		return{
			pageLoading 			: false,
			savingsList				: this.dataTransaction,
			loadingTable 			: false,
			search 					: "",
			loadingTransTable 		: false,
			accountTransactionList 	: [],
			accountSelected 		: {},
			samplePrint 			: ""
		}
	},
	created(){
		//this.getAccountList()
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
		getSummaries(param) {
	        const { columns, data } = param;
	        const sums = [];
	        columns.forEach((column, index) => {
	          	if (index === 0) {
		            sums[index] = 'TOTAL';
		            return;
	          	}
	          	if(index === 1){
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
    	printBalance(type){
    		if(!this.accountSelected){
    			new Noty({
                    theme: 'relax',
                    type: "error",
                    layout: 'topRight',
                    text: "Please select account.",
                    timeout: 3000
                }).show();
                return
    		}

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

			this.$API.General.printBalance(data, type, 'Savings')
			.then(result => {
				let res = result.data
				if(type == 'pdf'){
					this.exporter(type, 'Savings Account', res)
				}
				else if(type == 'print'){
					this.winPrint(res.data, 'Savings Account')
				}
			})
			.catch(err => { console.log(err)})
			.then(_ => { this.pageLoading = false })
    	}
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