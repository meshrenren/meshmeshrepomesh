<template>
	<div class="savings-deposit-form" v-loading = "pageLoading">
		<div class="box box-info">
            <div class="box-header with-border">
              	<h3 class="box-title">Dividend And Refund</h3>
            </div>
            <div class = "box-body">
                <el-form label-position="right" label-width="200px" :model="reportForm" ref = "reportForm">
                <el-row :gutter = "20">         
                    <el-col :span="12">
                        <el-form-item label="Dividend">
                            <el-input-number v-model="reportForm.dividend" controls-position = "right" placeholder = "Dividend">
                            </el-input-number>
                        </el-form-item>
                        <el-form-item label="Patronage Refund">
                            <el-input-number v-model="reportForm.patronage_refund" controls-position = "right" placeholder = "Patronage Refund">
                            </el-input-number>
                        </el-form-item>
                    </el-col>
                </el-row>
                </el-form>
             </el-form>
            	<el-row :gutter="20">
            		<el-col :span="24">
            			<div class = "right-toolbar mb-10">       			
            			</div>
            			<!-- <el-input class = "mb-10" v-model="search" size="mini" placeholder="Search account name"/> -->
						<el-table 
							:data="tableList"
							style="width: 100%" 
							height="450px" stripe border 
							v-loading = "loadingTable"
							width = "100%">
				            <!-- <el-table-column label="ID" width="100">
				                <template slot-scope="scope">
				                    <span style="margin-left: 10px">{{ scope.row.accountnumber }}</span>
				                </template>
				            </el-table-column> -->
				            <el-table-column label="Name"  width="230">
				                <template slot-scope="scope">
				                   	<span v-if = "scope.row.station_name" >{{ scope.row.station_name }}</span>
                                    <span v-else >{{ scope.row.member.fullname }}</span>
				                </template>
				            </el-table-column>
                            <el-table-column label="Average Balance" prop = "average_running_balance">
                                <template slot-scope="scope">
                                   <span >{{ $nf.formatNumber(scope.row.average_running_balance, 2) }}</span>
                                </template>
                            </el-table-column>
                            <el-table-column label="Interest Earned" header-align = "center">
                                <el-table-column
                                    v-for="item in loandProducts"
                                    :key = "item.id"
                                    :prop="'loan_id_' + item.id"
                                    :label="item.shor_name"
                                    header-align = "center">
                                    <template slot-scope="scope">
                                       <span >{{ $nf.formatNumber(scope.row['loan_id_' + item.id], 2) }}</span>
                                    </template>
                                </el-table-column>
                            </el-table-column>

                            <el-table-column label="Total Interest Earned" prop = "totalLoanInterest">
                                <template slot-scope="scope">
                                   <span >{{ $nf.formatNumber(scope.row.totalLoanInterest, 2) }}</span>
                                </template>
                            </el-table-column>

                            <el-table-column label="Patronage Refund" prop = "patronage_refund">
                                <template slot-scope="scope">
                                   <span >{{ $nf.formatNumber(scope.row.patronage_refund, 2) }}</span>
                                </template>
                            </el-table-column>

                            <el-table-column label="Total (Dividend & Patronage Refund)" prop = "total_dpf">
                                <template slot-scope="scope">
                                   <span >{{ $nf.formatNumber(scope.row.total_dpf, 2) }}</span>
                                </template>
                            </el-table-column>

                            <el-table-column label="5% Retention" prop = "retention">
                                <template slot-scope="scope">
                                   <span >{{ $nf.formatNumber(scope.row.retention, 2) }}</span>
                                </template>
                            </el-table-column>

                            <el-table-column label="Div. & Pat. Refund (After 5% Retention)" prop = "dpf_after_retention">
                                <template slot-scope="scope">
                                   <span >{{ $nf.formatNumber(scope.row.dpf_after_retention, 2) }}</span>
                                </template>
                            </el-table-column>
				        </el-table>
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
	import fileExport from '../../mixins/fileExport'  

export default {
	props: ['pageData'],
	mixins: [fileExport],
	data: function () {
        let stations = [11, 7, 5, 8, 9, 14, 13, 12, 10, 1, 2, 16]
        let arrStation = [];
        _forEach(stations, st => {
            let arr = this.pageData.stationList.find(fn => { return st == Number(fn.id)})
            if(arr) arrStation.push(arr)
        })
		return{
			pageLoading 			: false,
			accountList				: [],
			loadingTable 			: false,
            reportForm              : {
                dividend : null,
                patronage_refund : null
            },
            loandProducts           : this.pageData.loandProducts,
            stationList             : arrStation,
		}
	},
	created(){
		
	},
    mounted(){
        this.getAccountList()
    },
	computed:{
		tableList(){
            let list = cloneDeep(this.accountList)
            let dividendNum = this.reportForm.dividend
            let refundNum = this.reportForm.patronage_refund

            _forEach(list, acc =>{

                _forEach(acc['loanAccounts'], loan =>{
                    acc['loan_id_'+loan.loan_id] = loan.totalInterestEarned
                })

                acc['dividend'] =  acc.average_running_balance * dividendNum
                acc['patronage_refund'] = acc.totalLoanInterest * refundNum
                acc['total_dpf'] = parseFloat(acc['dividend']) + parseFloat(acc['patronage_refund'])
                acc['retention'] = acc['total_dpf'] * .05
                acc['dpf_after_retention'] = parseFloat(acc['total_dpf']) - parseFloat(acc['retention'])
            })

            let stList = []
            let listCloneDeep = cloneDeep(list)
            _forEach(this.stationList, st =>{
                let getAccs = listCloneDeep.filter(fn => { return fn.member.station_id == st.id})
                let arr = {
                    station_name : st.name
                }
                stList.push(arr)
                stList = stList.concat(getAccs)
            })

            return stList
        }
	},
	methods:{
        getAccountList(){

      		this.pageLoading = true

      		let data = {

			}
			console.log('data', data)
            this.$API.Report.getDividendRefund()
            .then(result => {
                let res = result.data
                console.log('res', res)
                this.accountList = res.data
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