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
                    	<el-table
							:data="tableData"
				            border striped
				            style="width: 100%"
				            height = "500px"
				    		show-summary >
				            <el-table-column
				                prop="fullname" fixed>   
				                <template slot="header" slot-scope="scope">
				                    <el-input v-model="nameSearch" size="mini" placeholder="Search Member"/>
				                </template>                         
				            </el-table-column>
				            <el-table-column v-for="item in columnsList"
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

export default {
	props:{
		pageData : {
			type : Object,
			require : true
		},
	},
	data(){
		return{
			memberList 			: this.pageData.memberList,
			stationList 		: this.pageData.stationList,
			columnsList 		: [],
			loadingPage 		: false,
			nameSearch 			: null,
			selectForm 			: {station : null},
			filterMember 		: "",
			checkedMember 		: [],
			sealectAllEmp 		: false
		}
	},
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
			let list = []
			_forEach(mem, mb =>{
				let getMem = this.memberList.find(fn => Number(fn.id) == Number(mb))
				let arr = {}
				if(getMem){
					arr.fullname = getMem.fullname
					list.push(arr)
				}
			})
			return list
		}
	},
	methods:{
		selectMemChange(val){
			let arr = []
			if(val){
				_forEach(this.employeSelected, emp=>{
					arr.push(emp.id)
				})				
			}
			this.checkedMember = arr
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