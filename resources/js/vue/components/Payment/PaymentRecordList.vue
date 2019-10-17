<template>
	<div class="payment-record" v-loading = "loadingPage">
		<el-table
            :data="setTableData"
            border striped
            style="width: 100%"
            max-height = "500px"
            :summary-method="getSummaries"
    		show-summary >
            <el-table-column
                prop="account_name"
                label="Name">                            
            </el-table-column>
            <el-table-column v-for="item in setUpColumn"
            	:key = "item.key"
                :prop="item.key"
                :label="item.product_name">                            
            </el-table-column>
            <el-table-column
                prop="total"
                label="Total">                            
            </el-table-column>
        </el-table>

	</div>
</template>

<script>
	window.noty = require('noty')
    import Noty from 'noty'  

    import cloneDeep from 'lodash/cloneDeep'    
    import _forEach from 'lodash/forEach'
    import _reduce from 'lodash/reduce'

export default {
	props:{
		/* should include  accountList, allTotalAccount*/
		pageData : {
			type : Object,
			require : true
		},
	},
	data(){
		return{
			accountList 		: this.pageData.accountList,
			allTotalAccount 	: this.pageData.allTotalAccount,
			groupMemAccounts 	: [],
			loadingPage 		: false
		}
	},
	created(){
		this.groupAllAccount()
	},
	computed:{
		setUpColumn(){
			let accounts = this.accountList
			let list = []

			_forEach(accounts, acc=> {
				let key = acc.type + "_" + acc.product_id
				let arr = {
					particular_id : acc.particular_id,
					product_id : acc.product_id,
					product_name : acc.product_name,
					type : acc.type,
					key : key

				}

				list.push(arr)
			})

			return list
		},
		setTableData(){
			let accounts = this.accountList
			let memAccount = this.groupMemAccounts
			let list = []

			_forEach(memAccount, acc=>{
				let arr = {
					account_no : acc.account_no,
					account_name : acc.fullname,
					member_id : acc.member_id,
				}

				//Accounts
				let totalPayment = 0
				_forEach(acc.payments, mem => {
					arr[mem.type + "_" + mem.product_id] = Number(mem.amount)
					totalPayment = Number(totalPayment) + Number(mem.amount)
				})

				arr['total'] = totalPayment

				list.push(arr)

			})

			return list
		}
	},
	methods:{
		groupAllAccount(){
			let totalList = cloneDeep(this.allTotalAccount)
			let resultList = _reduce(totalList, (result, value, key) => {
				let fnAccIndex = result.findIndex(rs => {return String(rs.member_id)== String(value.member_id)})
				if(fnAccIndex >= 0){
					result[fnAccIndex].payments.push(value)
				}
				else{
					let arr = {
						account_no : value.account_no,
						fullname : value.fullname,
						member_id : value.member_id,
						payments : [value]
					}

					result.push(arr)
				}
				return result

			}, [])

			this.groupMemAccounts = resultList
		},
		
		getSummaries(param){
			const { columns, data } = param;
	        const sums = [];

	        columns.forEach((column, index) => {
	          	if (index === 0) {
	            	sums[index] = 'Total';
	            	return;
	          	}

	          	const values = data.map(item => Number(item[column.property]));
	          	if (!values.every(value => isNaN(value))) {
	            	sums[index] = '$ ' + values.reduce((prev, curr) => {
		              	const value = Number(curr);
		              	if (!isNaN(value)) {
		                	return prev + curr;
		              	} else {
		                	return prev;
		              	}
		            }, 0);
	          	} else {
	            	sums[index] = 'N/A';
	          	}
	        });

	        return sums;
		}
	}
}

</script>