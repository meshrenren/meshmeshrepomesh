<template>
	<div class="payment-record" v-loading = "loadingPage">
		<el-table
            :data="setTableData"
            border striped
            style="width: 100%"
            max-height = "500px">
            <el-table-column
                prop="account_name"
                label="Name">                            
            </el-table-column>
            <el-table-column v-for="item in setUpColumn"
                :prop="item.key"
                :label="item.product_name">                            
            </el-table-column>
        </el-table>
	</div>
</template>

<script>
	window.noty = require('noty')
    import Noty from 'noty'  

    import cloneDeep from 'lodash/cloneDeep'    
    import _forEach from 'lodash/forEach'

export default {
	props:{
		dataAccountList : {
			type : Array,
			require : true
		},
		dataAllTotalAccount : {
			type : Array,
			require : true
		}
	},
	data(){
		return{
			accountList 		: this.dataAccountList,
			allTotalAccount 	: this.dataAllTotalAccount
		}
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
			let totalList = this.allTotalAccount
			let list = []

			_forEach(accounts, acc=>{

			})

			return list
		}
	},
	methods:{

	}
}

</script>