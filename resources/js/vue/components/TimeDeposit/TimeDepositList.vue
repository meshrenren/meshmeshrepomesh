<template>
	<div class="time-deposit-list">
		<div class="box box-info">
            <div class="box-header with-border">
              	<h3 class="box-title">Time Deposit Accounts</h3>
            </div>
	        <div class = "box-body">
				<h4>{{ header }}</h4>
				<el-row :gutter = "20">
					<el-col :span = "14">
						<el-input v-model="nameKey" placeholder = "Enter account name to search"></el-input>
						<el-table :data="accountList" style="width: 100%" stripe border>
				            <el-table-column label="Account Name">
				                <template slot-scope="scope">
				                    <span>{{ scope.row.member.fullname }}</span>
				                   	<div v-if = "scope.row.is_mature">
				                   		<span class="label label-danger">Matured</span>
				                   	</div>
				                </template>
				            </el-table-column>
				            <el-table-column label="Account No">
				                <template slot-scope="scope">
				                    <span{{ scope.row.accountnumber }}</span>
				                </template>
				            </el-table-column>
				            <el-table-column label="Amount">
				                <template slot-scope="scope">
				                    <span>{{ scope.row.amount }}</span>
				                </template>
				            </el-table-column>
				            <el-table-column label="Date Open">
				                <template slot-scope="scope">
				                    <span>{{ $df.formatDate(scope.row.date_created, "MMMM DD, YYYY") }}</span>
				                </template>
				            </el-table-column>
				            <el-table-column label="Term (days)">
				                <template slot-scope="scope">
				                    <span>{{ scope.row.term }}</span>
				                </template>
				            </el-table-column>
				            <!-- <el-table-column label="Maturity Date">
				                <template slot-scope="scope">
				                    <span style="margin-left: 10px">{{ $df.formatDate(scope.row.maturity_date, "MMMM DD, YYYY") }}</span>
				                </template>
				            </el-table-column> -->
				            <el-table-column label="Action">
				                <template slot-scope="scope">
				                    <el-button type = "primary" @click = "selectAccount(scope.row)">Select</el-button>
				                </template>
				            </el-table-column>
		   				</el-table>
					</el-col>
					<el-col :span = "10">
						<el-row :gutter = "5">
							<el-col :span = "14">
								<label>Account Name</label>
								<el-input v-model="selectedAccount.account_name" :disabled = "true"></el-input>
							</el-col>
							<el-col :span = "10">
								<label>Account Number</label>
								<el-input v-model="selectedAccount.accountnumber" :disabled = "true"></el-input>
							</el-col>
						</el-row>

						<el-row :gutter = "5">
							<el-col :span = "12">
								<label>Amount</label>
								<el-input v-model="selectedAccount.amount" :disabled = "true"></el-input>
							</el-col>
							<el-col :span = "12">
								<label>Date Open</label>
								<el-input v-model="selectedAccount.created" :disabled = "true"></el-input>
							</el-col>
							<el-col :span = "12">
								<label>Terms</label>
								<el-input v-model="selectedAccount.term" :disabled = "true">
									<el-button slot="append"> days</el-button>
								</el-input>
							</el-col>
							<el-col :span = "12">
								<label>Maturity Days</label>
								<el-input v-model="selectedAccount.matured" :disabled = "true">
								</el-input>
							</el-col>
							<el-col :span = "24" v-if = "selectedAccount.is_matured">
								<div class="callout callout-danger">
					                <h4>Account Matured!</h4>
					            </div>
					            <el-button type = "primary" @click = "calculate(scope.row)">Calculate</el-button>
							</el-col>
						</el-row>
					</el-col>
				</el-row>
			</div>
		</div>
	</div>
</template>

<script>
	window.noty = require('noty');
    import axios from 'axios'
    import Noty from 'noty'
    import cloneDeep from 'lodash/cloneDeep' 
  	import _forEach from 'lodash/forEach'

export default {
	props: ['dataTimeDepositAccounts', 'typeList', 'header'],
	data: function () {

		return {
			tdAccounts 		: this.dataTimeDepositAccounts,
			selectedAccount	: {},
			nameKey 		: null,
			today 			: moment(new Date())
		}
	},
    computed: {
        accountList(){   
        	let vm = this   
            let datalist = this.tdAccounts
            let filterKey = this.nameKey
            let currTimestamp = this.$df.formatDate(cloneDeep(this.today), 'X')

			_forEach(datalist, function(element, index) {
				let matureTiemstamp = vm.$df.formatDate(element.maturity_date, 'X')
				element.is_mature = false
				if(currTimestamp >= matureTiemstamp){
					element.is_mature = true
				}
			})

            if (filterKey) {
                if(datalist){
                    datalist = datalist.filter(function (row) {
                        return String(row.fullname).toLowerCase().indexOf(filterKey) > -1
                    })
                }
            }

            return datalist
        }
    },
	methods:{
		selectAccount(account){
			console.log("account", account)
			let acc = cloneDeep(account)
			this.selectedAccount = acc
			this.selectedAccount.account_name = acc.member.fullname
			this.selectedAccount.created = this.$df.formatDate(acc.date_created, "MMMM DD, YYYY")
			this.selectedAccount.matured = this.$df.formatDate(acc.maturity_date, "MMMM DD, YYYY")
		},
		calculate(data){
			console.log(data)
			/*var a = moment([2007, 0, 29]);
			var b = moment([2007, 0, 28]);
			a.diff(b, 'days') */
		}
	}
}
</script>

<style lang="scss">
  	@import '../../assets/site.scss';
  	@import '~noty/src/noty.scss';

  	.time-deposit-list{

		.el-select{
			width: 100%;
		}

		.el-input-number{
			width: 100%;

			input{
				text-align: left;
			}
			
		}
	}
</style>