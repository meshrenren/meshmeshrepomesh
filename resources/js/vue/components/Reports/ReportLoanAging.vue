<template>
	<div class = "report-loan-aging">
		<div class="box box-info" v-loading = "pageLoading">
            <div class="box-header with-border">
              	<h3 class="box-title">Loan Aging</h3>
            </div>
	        <div class = "box-body">
	        	<el-row :gutter = "20">
	        		<el-col :span = "24">
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
						</el-form>
	        		</el-col>
	        		<el-col :span="24">
	        			<div class = "right-toolbar">
            				<el-button type = "default" @click = "printForm('print')">PRINT</el-button>            			
            			</div>
            			<div v-for="loan in loanAgingList" :key="parseInt(loan.id)" class = "mt-10">
            				<template v-if = "loan.stationAging && loan.stationAging.length > 0">
	            				<label>{{ loan.product_name }}</label>
	            				<div v-for="station in loan.stationAging" :key="'prod_'+loan.id+'_station_'+station.station_id" class = "mb-10">
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

							            <el-table-column label="1-3 Months" prop = "one_three" >  
							                <template slot-scope="scope"> 
							                	{{ $nf.formatNumber(scope.row.one_three, 2) }} 
							                </template>                      
							            </el-table-column>

							            <el-table-column label="4-6 Months" prop = "four_six" >  
							                <template slot-scope="scope"> 
							                	{{ $nf.formatNumber(scope.row.four_six, 2) }} 
							                </template>                      
							            </el-table-column>

							            <el-table-column label="7-12 Months" prop = "seven_twelve" >  
							                <template slot-scope="scope"> 
							                	{{ $nf.formatNumber(scope.row.seven_twelve, 2) }} 
							                </template>                      
							            </el-table-column>

							            <el-table-column label="Over 1 Year" prop = "over_one_year"  >  
							                <template slot-scope="scope"> 
							                	{{ $nf.formatNumber(scope.row.over_one_year, 2) }} 
							                </template>                      
							            </el-table-column>
							        </el-table>
	            				</div>
	            				<table class = "table sub-total">
						        	<tbody>
						        		<tr>
						        			<th width="20%">SUB TOTAL</th>
						        			<td width="20%">{{ $nf.formatNumber(loan.totalAging.one_three, 2) }}</td>
						        			<td width="20%">{{ $nf.formatNumber(loan.totalAging.four_six, 2) }}</td>
						        			<td width="20%">{{ $nf.formatNumber(loan.totalAging.seven_twelve, 2) }}</td>
						        			<td width="20%">{{ $nf.formatNumber(loan.totalAging.over_one_year, 2) }}</td>
						        		</tr>
						        	</tbody>
						        </table>
							    <hr>
            				</template>
					    </div>

				        <table class = "table grand-total">
				        	<tbody>
				        		<tr>
				        			<th width="20%">GRAND TOTAL</th>
				        			<td width="20%">{{ $nf.formatNumber(grandAllTotal.one_three, 2) }}</td>
				        			<td width="20%">{{ $nf.formatNumber(grandAllTotal.four_six, 2) }}</td>
				        			<td width="20%">{{ $nf.formatNumber(grandAllTotal.seven_twelve, 2) }}</td>
				        			<td width="20%">{{ $nf.formatNumber(grandAllTotal.over_one_year, 2) }}</td>
				        		</tr>
				        	</tbody>
				        </table>
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
		let searchForm = { selectProduct : null}
		return {
			searchForm 		: searchForm,
			formRule 		: {},
			pageLoading 	: false,
			loanProducts 	: this.pageData.loanProducts,
			stationList 	: this.pageData.stationList,
			agingList 		: [],
			currMoment 		: moment(this.$systemDate)
		}
	},
	created(){
		this.getLoanAging()
	},
    computed:{
        loanAgingList(){
        	let list = cloneDeep(this.loanProducts)
        	let loans = cloneDeep(this.agingList)
        	let currentDate = this.currMoment
        	
        	let grandTotal = {one_three : 0, four_six : 0, seven_twelve : 0, over_one_year : 0}
        	_forEach(loans, ln =>{
        		if(ln.release_date){
        			let loanRelease = moment(ln.release_date)
					let monDiff = currentDate.diff(loanRelease, 'months'); 
					let principalBal = parseFloat(ln.principal_balance)

					if(monDiff >= 1){

						let arr = ln
						arr.fullname = arr.member.fullname
						arr.station_id = arr.member.station_id
						arr.month_diff = monDiff
						if(monDiff >= 1 && monDiff <= 3){
							arr.one_three = principalBal
							grandTotal.one_three += parseFloat(principalBal)
						}
						else if(monDiff >= 4 && monDiff <= 6){
							arr.four_six = principalBal
							grandTotal.four_six += parseFloat(principalBal)
						}
						else if(monDiff >= 7 && monDiff <= 12){
							arr.seven_twelve = principalBal
							grandTotal.seven_twelve += parseFloat(principalBal)
						}
						else if(monDiff >= 12){
							arr.over_one_year = principalBal
							grandTotal.over_one_year += parseFloat(principalBal)
						}

						//get product belong
						let getProdInd = list.findIndex(fi => { return fi.id == ln.loan_id})
						if(getProdInd >= 0){
							if(list[getProdInd].agingList == undefined){
								this.$set(list[getProdInd], 'agingList', [])
							}

							if(list[getProdInd].stationAging == undefined){
								this.$set(list[getProdInd], 'stationAging', [])
							}

							let fnStation = list[getProdInd].stationAging.findIndex(fi => { return fi.station_id == arr.station_id})
							if(fnStation >= 0){
								list[getProdInd].stationAging[fnStation].list.push(arr)
							}
							else{

								let getStation = this.stationList.find(fi => { return fi.id == arr.station_id})
								let arr2 = {
									station_id : arr.station_id,
									list : [arr],
									station_name : getStation ? getStation.name : "No Station"
								}
								list[getProdInd].stationAging.push(arr2)
							}

							list[getProdInd].agingList.push(arr)

							//Total per product
							if(list[getProdInd].totalAging == undefined){
								this.$set(list[getProdInd], 'totalAging', {one_three : 0, four_six : 0, seven_twelve : 0, over_one_year : 0})
							}

							list[getProdInd].totalAging.one_three += arr.one_three ? parseFloat(arr.one_three) : 0
							list[getProdInd].totalAging.four_six += arr.four_six ? parseFloat(arr.four_six) : 0
							list[getProdInd].totalAging.seven_twelve += arr.seven_twelve ? parseFloat(arr.seven_twelve) : 0
							list[getProdInd].totalAging.over_one_year += arr.over_one_year ? parseFloat(arr.over_one_year) : 0

							
						}

					}
        		}
				  		
        	})

        	_forEach(list, ln =>{
        		if(ln.agingList){
        			ln.agingList = _sortBy(ln.agingList, [o => { return o.fullname;}])
        		}
        		if(ln.stationAging){
        			ln.stationAging = _sortBy(ln.stationAging, [o => { return o.station_id;}])
        		}
        	})

        	if(this.searchForm.selectProduct){
        		list = list.filter(fn => { return fn.id == this.searchForm.selectProduct})
        	}

        	return list
        },
        grandAllTotal(){
        	let total = {one_three : 0, four_six : 0, seven_twelve : 0, over_one_year : 0}
        	_forEach(this.loanAgingList, ln => {
        		total.one_three += ln.totalAging && ln.totalAging.one_three ? parseFloat(ln.totalAging.one_three) : 0
        		total.four_six += ln.totalAging && ln.totalAging.four_six ? parseFloat(ln.totalAging.four_six) : 0
        		total.seven_twelve += ln.totalAging && ln.totalAging.seven_twelve ? parseFloat(ln.totalAging.seven_twelve) : 0
        		total.over_one_year += ln.totalAging && ln.totalAging.over_one_year ? parseFloat(ln.totalAging.over_one_year) : 0
        	})

        	return total
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
		getLoanAging(){
			let form = cloneDeep(this.searchForm)

			console.log('Test', form)
			this.pageLoading = true

            this.$API.Report.getLoanAging(form)
            .then(result => {
                let res = result.data
                this.agingList = res.data
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
    			loanList : this.loanAgingList,
    			grandTotal : this.grandAllTotal,
    			header : [
    				{label : "Name", prop : "fullname"},
    				{label : "1-3 Months", prop : "one_three", type : 'number'},
    				{label : "4-6 Months", prop : "four_six", type : 'number'},
    				{label : "7-12 Months", prop : "seven_twelve", type : 'number'},
    				{label : "Over 1 Year", prop : "over_one_year", type : 'number'},
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
.report-loan-aging{

	.sub-total{
		color: #0483ce;
		font-weight: bold;
	}

	.grand-total{
		color: #e43939;
		font-weight: bold;
	}
}
</style>