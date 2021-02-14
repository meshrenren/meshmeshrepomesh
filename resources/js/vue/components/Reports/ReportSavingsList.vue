<template>
	<div class = "report-savings-list">
		<div class="box box-info" v-loading = "pageLoading">
            <div class="box-header with-border">
              	<h3 class="box-title">Savings List</h3>
            </div>
	        <div class = "box-body">
	        	<el-row :gutter = "20">
	        		<!-- <el-col :span = "12">
	        			<el-form :model="searchForm" :rules = "formRule" ref="searchForm" label-width="150px" class="demo-ruleForm" >
							<el-form-item label="Loan Product"  prop = "selectProduct" >
								<el-select v-model="searchForm.selectProduct"
									placeholder="Select"
									filterable>
									<el-option
                                        label="All"
                                        :value="null">
                                    </el-option>
									<el-option
                                        v-for="item in stationDropdown"
                                        :key="parseInt(item.id)"
                                        :label="item.product_name"
                                        :value="parseInt(item.id)">
                                    </el-option>
								</el-select>
							</el-form-item>
						</el-form>
	        		</el-col> -->

	        		<el-col :span="24">
	        			<div class = "right-toolbar">
            				<el-button type = "default" @click = "printForm('print')">PRINT</el-button>            			
            			</div>

            			<div v-for="stat in stationSavingsList" :key="'station_'+stat.id" class = "mt-10">
            				<div v-if = "stat.tableList && stat.tableList.length > 0">
	            				<label>{{ stat.name }}</label>
	            				<el-table
									:data="stat.tableList"
						            border striped
						            style="width: 100%"
						            class = "mt-5"
						            max-height = "500px"
									show-summary 
									width = "100%"
									:summary-method="getSummaries">

									<el-table-column label="Name">      
						                <template slot-scope="scope"> 
						                	{{ scope.row.account_name }} 
						                </template>                       
						            </el-table-column>

						            <el-table-column label="Balance" prop = "balance" >  
						                <template slot-scope="scope"> 
						                	{{ $nf.formatNumber(scope.row.balance, 2) }} 
						                </template>                      
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
window.noty = require('noty')
import Noty from 'noty'
import cloneDeep from 'lodash/cloneDeep'    
import _forEach from 'lodash/forEach'
import _sortBy from 'lodash/sortBy'

import swalAlert from '../../mixins/swalAlert.js'
import _message from '../../mixins/messageDialog.js'
import fileExport from '../../mixins/fileExport'

export default {
	props:{
        pageData : {
            type : Object,
            require : true
        },
    },
    mixins: [swalAlert, _message, fileExport],
	data: function () {
		let searchForm = { selectStation : null, searchName : null}
		let stationDropdown = cloneDeep(this.pageData.stationList)
		stationDropdown.push({id : null, name : "No Station"})

		return {
			searchForm 		: searchForm,
			formRule 		: {},
			pageLoading 	: false,
			savingsList 	: this.pageData.accountList,
			stationList 	: this.pageData.stationList,
			currMoment 		: moment(this.$systemDate),
			stationDropdown : stationDropdown
		}
	},
	mounted(){
		this.getAccountList()
	},
    computed:{
        stationSavingsList(){

        	let list = cloneDeep(this.stationList)
        	let loans = cloneDeep(this.savingsList)
        	let currentDate = this.currMoment
        	
        	_forEach(loans, ln =>{
        		let arr = ln
				arr.account_name = arr.account_name ? arr.account_name : (arr.member ? arr.member.fullname : null)
				arr.station_id = arr.member ? arr.member.station_id : null

				//get product belong
				let getStationInd = list.findIndex(fi => { return fi.id == arr.station_id}) 
				if(getStationInd >= 0){
					if(list[getStationInd].tableList == undefined){
						this.$set(list[getStationInd], 'tableList', [])
					}

					list[getStationInd].tableList.push(arr)

				}
				else{
					let arr2 = {
						id : arr.station_id,
						tableList : [arr],
						name : "No Station"
					}
					list.push(arr2)
				}

        	})

        	/*if(this.searchForm.selectStation){
        		list = list.filter(fn => { return fn.id == this.searchForm.selectStation})
        	}*/

        	//list = _sortBy(list, [o => { return o.id;}])

        	return list
        },
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
		printForm(type){

    		this.pageLoading = true

    		let data = {
    			title : "Savings List",
    			date : this.$df.formatDate(this.currMoment, "YYYY-MM-DD"),
    			dataList : this.stationSavingsList,
    			header : [
    				{label : "Name", prop : "account_name"},
    				{label : "Balance", prop : "balance", type : 'number'},
    			]
    		}

			this.$API.Report.printTableList(data, type)
			.then(result => {
				let res = result.data
				if(type == 'pdf'){
					this.exporter(type, 'Savings List', res)
				}
				else if(type == 'print'){
					this.winPrint(res.data, 'Savings List')
				}
			})
			.catch(err => { console.log(err)})
			.then(_ => { this.pageLoading = false })

    		//window.location.href = this.$baseUrl+"/savings/print-withdraw?account_no="+this.accountDetails.account_no;
    	}
    }
}

</script>
<style lang="scss">
.report-loan-arrears{

	.sub-total{
		color: #0483ce;
		font-weight: bold;
	}

	.grand-total{
		color: #e43939;
		font-weight: bold;
	}

	.el-form-item{
		margin-bottom: 0px;
	}
}
</style>