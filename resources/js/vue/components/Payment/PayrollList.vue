<template>
	<div class="payroll-list" v-loading = "loadingPage">
		<el-row :gutter = "20">
			<el-col :span="6">
				<div class="box box-default">
                    <div class="box-header">
                        <h3 class="box-title">Select Member</h3>
                    </div>
                    <div class="box-body payment-entry-list">
                    	<el-form :model="selectForm" style="margin-left: 0px" ref="select-form">
							<el-form-item label="">
								<el-select v-model="selectForm.station" 
									placeholder="Select" 
									class="full-width">
								    <el-option 
										label="All"
										:value="null">
									</el-option>
									<el-option
										v-for="item in stationList"
										:key="parseInt(item.id)"
										:label="item.name"
										:value="parseInt(item.id)">
									</el-option>
								</el-select>
							</el-form-item>
							<el-form-item label="">
								<!-- <el-input placeholder="Search" v-model="filterMember"></el-input> -->
								<el-checkbox v-model="sealectAllEmp" @change = "selectMemChange">
						  			Select All
						  		</el-checkbox>
								<div class = "member-container">
									<el-row>
										<el-checkbox-group v-model="checkedMember">
											<el-checkbox 
												v-for="item in memberSelected"
												:key="parseInt(item.id)"
												:label="item.id">
												<span>{{item.fullname}}</span>
											</el-checkbox>
										</el-checkbox-group>
									</el-row> 
								</div>
							</el-form-item>
						</el-form>
                    </div>
                </div>
			</el-col>
			<el-col :span="18">
				<div class="box box-info">
					<div class="box-header">
                        <h3 class="box-title">Payroll List</h3>
                    </div>
                    <div class="box-body">
                        <div class = "toolbar-right" style="margin-top: 7px; margin-right: 16px;">
	        				<el-button size="mini" @click="setPayroll()">Set</el-button>
	        				<el-button type = "info" size="mini" @click="exportExcel()">Export</el-button>
	        			</div>
                    	<el-table
							:data="tableData"
				            border striped
				            style="width: 100%"
    						max-height="500" >

				            <el-table-column
				            	width = "150px"
				                prop="fullname">                          
				            </el-table-column>

				            <el-table-column v-for="item in columnList"
				            	width="100px"
				            	:key = "item.key"
				                :prop="item.key"
				                :label="item.label">       
				                <template slot-scope="scope"> 
				                	{{ $nf.formatNumber(scope.row[item.key], 2) }} 
				                </template>                        
				            </el-table-column>
				        </el-table>
                    </div>
				</div>
			</el-col>
		</el-row>
	</div>
</template>

<script>
	window.noty = require('noty')
    import Noty from 'noty'  

    import cloneDeep from 'lodash/cloneDeep'    
    import _forEach from 'lodash/forEach'

    import fileExport from '../../mixins/fileExport'

export default {
	props:{
		pageData : {
			type : Object,
			require : true
		},
	},
	data(){
		let col = cloneDeep(this.pageData.columnList)
		return{
			memberList 			: this.pageData.memberList,
			stationList 		: this.pageData.stationList,
			defColumnList 		: col,
			columnList 			: col,
			loanColumn 			: [],
			loadingPage 		: false,
			nameSearch 			: null,
			selectForm 			: {station : null},
			filterMember 		: "",
			checkedMember 		: [],
			sealectAllEmp 		: false,
			memberLoan 			: []
		}
	},
	mixins: [fileExport],
	created(){
	},
	computed:{
		memberSelected(){
			let list = cloneDeep(this.memberList)

			if(this.selectForm.station){
				list = list.filter(lt => { return Number(lt.station_id) == Number(this.selectForm.station)})
			}

			return list
		},
		tableData(){
			let mem = this.checkedMember
			let loan = this.memberLoan
			let list = []
			_forEach(mem, mb =>{
				let getMem = this.memberList.find(fn => Number(fn.id) == Number(mb))
				let arr = {}
				if(getMem){
					let getLoan = loan.find(fn => Number(fn.member_id) == Number(mb))
					if(getLoan){
						arr = getLoan
					}
					arr.fullname = getMem.fullname
					list.push(arr)
				}
			})
			return list
		}
	},
	methods:{
		selectMemChange(val){
			console.log('selectMemChange', val)
			let arr = []
			if(val){
				_forEach(this.memberSelected, emp=>{
					arr.push(emp.id)
				})				
			}
			this.checkedMember = arr
		},
		setPayroll(){
			console.log('setPayroll')
			this.loadingPage = true

			let mem = cloneDeep(this.checkedMember)
			let params = {
				members : mem
			}

			this.$API.Payment.setPaymentPayroll(params)
            .then(result => {
                let res = result.data
                console.log('res', res)
                this.setEmpLoan(res.loanColumns)

                setTimeout(() => {
                	this.memberLoan = res.loanMember
                }, 1000);

            })
            .catch(err => { console.log(err)})
            .then(_ => { this.loadingPage = false })
		},
		setEmpLoan(loanCols){
			let colArr = cloneDeep(this.defColumnList)
			this.columnList = loanCols.concat(colArr)
		},
		exportExcel(){

			this.loadingPage = true

			let mem = cloneDeep(this.checkedMember)
			let headers = cloneDeep(this.columnList)
			headers.splice(0, 0, {key : 'fullname', label : "Name"})

			let title = "ALL STATION"
			if(this.selectForm.station){
				let getS = this.stationList.find(fn => Number(fn.id) == Number(this.selectForm.station))
				if(getS){
					title = getS.name + " STATION"
				}
			}
			let params = {
				data : this.tableData,
				headers : headers,
				title : title
			}

			this.$API.Report.payrollExport(params)
            .then(result => {
                this.exporter('xlsx', title + ' PAYROLL', result.data)

            })
            .catch(err => { console.log(err)})
            .then(_ => { this.loadingPage = false })
		}
	},
	watch:{
		
	}
}

</script>
<style lang="scss">
.payroll-list{

	.member-container{
		height: 400px;
		overflow: auto;
		border: 1px solid #dcdfe6;
		padding: 5px 5px;
		border-radius: 4px;

		.el-checkbox-group{
			line-height: 20px;
		}
	}
}
</style>