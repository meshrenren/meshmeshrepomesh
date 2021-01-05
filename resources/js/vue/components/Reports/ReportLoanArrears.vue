<template>
	<div class = "report-loan-arrears">
		<div class="box box-info" v-loading = "pageLoading">
            <div class="box-header with-border">
              	<h3 class="box-title">Loan Arrears</h3>
            </div>
	        <div class = "box-body">
	        	<el-row :gutter = "20">
	        		<el-col :span = "12">
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
                                        v-for="item in pageData.loanProducts"
                                        :key="parseInt(item.id)"
                                        :label="item.product_name"
                                        :value="parseInt(item.id)">
                                    </el-option>
								</el-select>
							</el-form-item>
							<!-- <el-form-item label="Search"  prop = "searchName" >
								<el-input placeholder="Search name" v-model="searchForm.searchName">
								</el-input>
							</el-form-item> -->
						</el-form>
	        		</el-col>
	        		<el-col :span="24">
	        			<div class = "right-toolbar">
            				<el-button type = "default" @click = "printForm('print')">PRINT</el-button>            			
            			</div>

            			<div v-for="loan in loanArrearsList" :key="parseInt(loan.id)" class = "mt-10">
            				<template v-if = "loan.stationArrears && loan.stationArrears.length > 0">
	            				<label>{{ loan.product_name }}</label>
	            				<div v-for="station in loan.stationArrears" :key="'prod_'+loan.id+'_station_'+station.station_id" class = "mb-10">
	            					<p style = "text-decoration: underline;">{{ station.station_name }}</p>
		            				<el-table
										:data="station.list"
							            border striped
							            style="width: 100%"
							            class = "mt-5"
							            max-height = "500px"
										show-summary 
										width = "100%"
										:summary-method="getSummaries">

							            <el-table-column label="Name">      
							                <template slot-scope="scope"> 
							                	{{ scope.row.member ? scope.row.member.fullname : "" }} 
							                </template>                       
							            </el-table-column>

							            <el-table-column label="Principal" prop = "principal" >  
							                <template slot-scope="scope"> 
							                	{{ $nf.formatNumber(scope.row.principal, 2) }} 
							                </template>                      
							            </el-table-column>

							            <el-table-column label="Balance" prop = "principal_balance" >  
							                <template slot-scope="scope"> 
							                	{{ $nf.formatNumber(scope.row.principal_balance, 2) }} 
							                </template>                      
							            </el-table-column>

							            <el-table-column label="Arrears" prop = "arrears" >  
							                <template slot-scope="scope"> 
							                	{{ $nf.formatNumber(scope.row.arrears, 2) }} 
							                </template>                      
							            </el-table-column>

							        </el-table>
							    </div>
							</template>

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
		let searchForm = { selectProduct : null, searchName : null}
		return {
			searchForm 		: searchForm,
			formRule 		: {},
			pageLoading 	: false,
			loanProducts 	: this.pageData.loanProducts,
			stationList 	: this.pageData.stationList,
			arrearsList 	: [],
			currMoment 		: moment(this.$systemDate)
		}
	},
	created(){
		this.getLoanArrears()
	},
    computed:{
        loanArrearsList(){

        	let list = cloneDeep(this.loanProducts)
        	let loans = cloneDeep(this.arrearsList)
        	let currentDate = this.currMoment
        	
        	_forEach(loans, ln =>{
        		let arr = ln
				arr.fullname = arr.member.fullname
				arr.station_id = arr.member.station_id

        		//get product belong
				let getProdInd = list.findIndex(fi => { return fi.id == ln.loan_id}) 
				if(getProdInd >= 0){
					if(list[getProdInd].arrearsList == undefined){
						this.$set(list[getProdInd], 'arrearsList', [])
					}

					if(list[getProdInd].stationArrears == undefined){
						this.$set(list[getProdInd], 'stationArrears', [])
					}

					let fnStation = list[getProdInd].stationArrears.findIndex(fi => { return fi.station_id == arr.station_id})
					if(fnStation >= 0){
						list[getProdInd].stationArrears[fnStation].list.push(arr)
					}
					else{

						let getStation = this.stationList.find(fi => { return fi.id == arr.station_id})
						let arr2 = {
							station_id : arr.station_id,
							list : [arr],
							station_name : getStation ? getStation.name : "No Station"
						}
						list[getProdInd].stationArrears.push(arr2)
					}

					list[getProdInd].arrearsList.push(arr)

				}
        	})

        	_forEach(list, ln =>{
        		if(ln.stationArrears){
        			ln.stationArrears = _sortBy(ln.stationArrears, [o => { return o.station_id;}])

        			/*_forEach(ln.stationArrears, lnStat =>{
        				lnStat.list = _sortBy(lnStat.list, [o => { return o.principal;}])
        			})*/
        		}
        	})

        	if(this.searchForm.selectProduct){
        		list = list.filter(fn => { return fn.id == this.searchForm.selectProduct})
        	}

        	/*if(this.searchForm.searchName){
        		let filterKey = this.searchForm.searchName
        		_forEach(list, ln =>{
        			ln.arrearsList = ln.arrearsList.filter(fn => { 
	        			return String(fn.fullname).toLowerCase().indexOf(filterKey) > -1
	        		})
        		})
        		

        	}*/

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
		getLoanArrears(){
			let form = cloneDeep(this.searchForm)

			console.log('Test', form)
			this.pageLoading = true

            this.$API.Report.getLoanArrears(form)
            .then(result => {
                let res = result.data
                this.arrearsList = res.data
            })
            .catch(err => {
                console.log(err)
            })
            .then(_ => { 
                this.pageLoading = false
            })
			
		},
		printForm(type){

    		this.pageLoading = true

    		let data = {
    			date : this.$df.formatDate(this.currMoment, "YYYY-MM-DD"),
    			loanList : this.loanArrearsList,
    			grandTotal : this.grandAllTotal,
    			header : [
    				{label : "Name", prop : "fullname"},
    				{label : "Principal", prop : "principal", type : 'number'},
    				{label : "Balance", prop : "principal_balance", type : 'number'},
    				{label : "Arrears", prop : "arrears", type : 'number'},
    			]
    		}

			this.$API.Report.printLoanAging(data, type)
			.then(result => {
				let res = result.data
				if(type == 'pdf'){
					this.exporter(type, 'Particular Payments', res)
				}
				else if(type == 'print'){
					this.winPrint(res.data, 'Particular Payments')
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