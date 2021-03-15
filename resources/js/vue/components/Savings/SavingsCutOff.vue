<template>
	<div class="savings-deposit-form" v-loading = "pageLoading">
		<div class="box box-info">
            <div class="box-header with-border">
              	<h3 class="box-title">Savings Account Cut Off</h3>
            </div>
            <div class = "box-body">
            	<el-row :gutter="20">
            		<el-col :span="24">
            			<div class = "right-toolbar mb-10">
            				<el-button type = "primary" @click = "setVoucher()">SAVE</el-button>     
            				<el-button type = "primary" @click = "printCutOff('print')">PRINT</el-button>           			
            			</div>
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
				                	<el-input v-model="scope.row.total_interest">
									</el-input>
				                   <!-- 	<span >{{ $nf.formatNumber(scope.row.total_interest, 2) }}</span> -->
				                </template>
				            </el-table-column>
				        </el-table>
            		</el-col>
            	</el-row>
            </div>
        </div>
        <voucher-view-form 
            :data-list = "voucherList"
            :gv-required = "true"
            :date-transact = "dateTransact"
            v-if="isShowVoucher"
            :visible.sync="isShowVoucher"
            @close="isShowVoucher = false"
            @processvoucher="processVoucher">
        </voucher-view-form>
    </div>
</template>
<script> 
	window.noty = require('noty')
    import Noty from 'noty'
    import cloneDeep from 'lodash/cloneDeep'  
    import _forEach from 'lodash/forEach'
	import fileExport from '../../mixins/fileExport'  

export default {
	props: ['dataTransaction', 'pageData'],
	mixins: [fileExport],
	data: function () {
		return{
			pageLoading 			: false,
			savingsList				: this.dataTransaction,
			loadingTable 			: false,
			search 					: "",
			loadingTransTable 		: false,
			voucherList 			: [],
			dateTransact 			: this.pageData.cutOff,
			year 					: this.pageData.cutOffYear,
			isShowVoucher 			: false,
			savingsToSave 			: []
		}
	},
	created(){
		//this.getAccountList()
	},
	computed:{
		totalInterestEarned(){
			let totalInterest = 0
    		let transactionToSave = []
    		_forEach(this.savingsList, savings =>{
    			if(savings.total_interest > 0){
    				totalInterest += parseFloat(savings.total_interest)
    				transactionToSave.push(savings)
    			}
    		})

    		return {transactionToSave , totalInterest}
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
    	setVoucher(){
    		this.savingsToSave = cloneDeep(this.totalInterestEarned.transactionToSave)

    		totalInterest = this.$nf.numberFixed(totalInterest)

    		let list = []
            //Set product name
            let arr = {particular_name: 'Interest Expense', amount: totalInterest, type : "DEBIT", account_no : null, account_name : "DILG XI EMPC" }
            list.push(arr)
            arr = {particular_name: 'Savings Deposit', amount: totalInterest, type : "CREDIT", account_no : null, account_name : "DILG XI EMPC" }
            list.push(arr)

    		this.voucherList = list
            
            this.isShowVoucher = true
    	},
    	printCutOff(type){
    		if(this.savingsList.length == 0){
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
    		let data = {
    			totalInterestEarned : this.totalInterestEarned.totalInterest,
    			transaction : this.savingsList
    		}

			this.$API.Savings.printCutOff(data, type)
			.then(result => {
				let res = result.data
				if(type == 'pdf'){
					this.exporter(type, 'Savings Interest Earned', res)
				}
				else if(type == 'print'){

					this.winPrint(res.data, 'Savings Interest Earned')
				}
			})
			.catch(err => { console.log(err)})
			.then(_ => { this.pageLoading = false })
    	},
    	
    	processVoucher(data){
            this.saveCutOff(data.gv_num, data.voucher_entries, data.transaction_date)
        },
        saveCutOff(gvNumber, voucherDetails, transaction_date){

      		this.pageLoading = true

      		let data = {
				savingsToSave: this.savingsToSave,
                voucherDetails : voucherDetails,
                gv_num : gvNumber,
                transaction_date : transaction_date
			}
			console.log('data', data)
            this.$API.Savings.saveCutoff(data)
            .then(result => {
                let res = result.data
                if(res.success){
                	location.reload()
                }
                else{
                	new Noty({
	                    theme: 'relax',
	                    type: "error",
	                    layout: 'topRight',
	                    text: "An error occured. Please contact administrator.",
	                    timeout: 3000
	                }).show();
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