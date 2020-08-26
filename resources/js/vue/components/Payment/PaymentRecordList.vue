<template>
	<div class="payment-record" v-loading = "loadingPage">
		<el-table
			:data="setTableData.filter(data => !nameSearch || data.fullname.toLowerCase().includes(nameSearch.toLowerCase()))"
            border striped
            style="width: 100%"
            max-height = "500px"
            :summary-method="getSummaries"
    		show-summary >
            <el-table-column
                prop="fullname" fixed>   
                <template slot="header" slot-scope="scope">
                    <el-input v-model="nameSearch" size="mini" placeholder="Search Member"/>
                </template>                          
            </el-table-column>
            <el-table-column v-for="item in setUpColumn"
            	:key = "item.key"
                :prop="item.key"
                :label="item.product_name">       
                <template slot-scope="scope"> 
                	{{ $nf.formatNumber(scope.row[item.key], 2) }} 
                </template>                        
            </el-table-column>
            <el-table-column
                prop="total"
                label="Total" fixed="right" >  
                <template slot-scope="scope"> 
                	{{ $nf.formatNumber(scope.row.total, 2) }} 
                </template>                      
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
			loadingPage 		: false,
			nameSearch 			: ""
		}
	},
	created(){
		this.groupAllAccount()
	},
	computed:{
		accMemList(){
            let totalAccnt = cloneDeep(this.allTotalAccount)
            let arrMem = []
            if(totalAccnt.length > 0){
                arrMem = Array.from(new Set(totalAccnt.map(t => { return t.member_id})))
                arrMem = arrMem.map(id => {
                    let getMem = totalAccnt.find(s => { return s.member_id == id})
                    return { id : id, fullname : getMem.fullname}
                })
            }
            
            return arrMem
        },
		setUpColumn(){
			let accounts = this.accountList
			let list = []

			_forEach(accounts, acc=> {
				let key = acc.type + "_" + acc.particular_id
				let arr = {
					particular_id : acc.particular_id,
					product_id : acc.product_id,
					product_name : acc.product_name,
					type : acc.type,
					key : key,
					is_prepaid : false

				}
				if(acc.is_prepaid !== undefined && acc.is_prepaid){
					arr.key = acc.type + "_PI" + "_" + acc.particular_id
					arr.is_prepaid = true
				}

				list.push(arr)
			})

			return list

		},
		setTableData(){
			let allTotals = cloneDeep(this.allTotalAccount)
            let accMem = cloneDeep(this.accMemList)
            let accColumn = cloneDeep(this.setUpColumn)

            let memRows = []

            _forEach(accMem, mem => {
                let arr = mem
                let sumTotal = 0
                _forEach(accColumn, col => {

                	let getAccAmount = allTotals.filter(rs => { return rs.type == col.type && rs.particular_id == col.particular_id && mem.id == rs.member_id})

                    if(col.type == "LOAN"){
                    	let getAccAmount = allTotals.filter(rs => { return rs.type == col.type && rs.product_id == col.product_id && mem.id == rs.member_id})

                    	if(col.is_prepaid){
	                    	getAccAmount = allTotals.filter(rs => { return rs.is_prepaid !== undefined && rs.is_prepaid && rs.type == col.type && rs.product_id == col.product_id && mem.id == rs.member_id})
	                    }else{
                    		getAccAmount = allTotals.filter(rs => { return (rs.is_prepaid === undefined || !rs.is_prepaid ) && rs.type == col.type && rs.product_id == col.product_id && mem.id == rs.member_id})
	                    }
                    }
                    

                    let sumAcc = _reduce(getAccAmount, function(result, val) {
                      return result + parseFloat(val.amount) ;
                    }, 0);

                    let key = col.key

                    arr[key] = parseFloat(sumAcc)
                    sumTotal = parseFloat(sumTotal) + parseFloat(sumAcc)
                })
                arr['total'] = Number(sumTotal).toFixed(2)
                memRows.push(arr)
            })

            return memRows
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
	            	let sumAmount = values.reduce((prev, curr) => {
		              	const value = Number(curr);
		              	if (!isNaN(value)) {
		                	return prev + curr;
		              	} else {
		                	return prev;
		              	}
		            }, 0);
		            sums[index] = ' ' + this.$nf.formatNumber(sumAmount, 2)
	          	} else {
	            	sums[index] = 'N/A';
	          	}
	        });

	        return sums;
		}
	},
	watch:{
		'pageData': function(val){
			this.accountList = val.accountList
			this.allTotalAccount = val.allTotalAccount
		}
	}
}

</script>