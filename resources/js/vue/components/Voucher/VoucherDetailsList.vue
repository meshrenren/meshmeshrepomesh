<template>
	<div class="voucher-details" v-loading = "loadingPage">
		<el-table
			:data="setTableData.filter(data => !nameSearch || data.fullname.toLowerCase().includes(nameSearch.toLowerCase()))"
            border striped
            style="width: 100%"
            max-height = "500px"
            :summary-method="getSummaries"
    		show-summary >
            <el-table-column
                prop="fullname">   
                <template slot="header" slot-scope="scope">
                    <el-input v-model="nameSearch" size="mini" placeholder="Search Member"/>
                </template>                          
            </el-table-column>
            <el-table-column
                prop="total"
                label="Total">  
                <template slot-scope="scope"> 
                	{{ scope.row.product_name }}
                </template>                      
            </el-table-column>
            <el-table-column
                prop="debit"
                label="Debit">  
                <template slot-scope="scope"> 
                	{{ $nf.formatNumber(scope.row.debit, 2) }} 
                </template>                      
            </el-table-column>
            <el-table-column
                prop="credit"
                label="Credit">  
                <template slot-scope="scope"> 
                	{{ $nf.formatNumber(scope.row.credit, 2) }} 
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
		/* should include  entryList, allTotalAccount*/
		pageData : {
			type : Object,
			require : true
		},
	},
	data(){
		return{
			entryList 			: this.pageData.entryList,
			allTotalAccount 	: this.pageData.allTotalAccount,
			groupMemAccounts 	: [],
			loadingPage 		: false,
			nameSearch 			: ""
		}
	},
	created(){
	},
	computed:{
		setTableData(){
            let totalAccnt = cloneDeep(this.allTotalAccount)
            
            
            return totalAccnt
        },
	},
	methods:{
		getSummaries(param){
			const { columns, data } = param;
	        const sums = [];

	        columns.forEach((column, index) => {
	          	if (index === 0) {
	            	sums[index] = 'Total';
	            	return;
	          	}
	          	else if(index === 2 || index === 3){
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
	          	}
	          	else{
	          		sums[index] = '';
	            	return;
	          	}

	          	
	        });

	        return sums;
		}
	},
	watch:{
		'pageData': function(val){
			this.entryList = val.entryList
			this.allTotalAccount = val.allTotalAccount
		}
	}
}

</script>