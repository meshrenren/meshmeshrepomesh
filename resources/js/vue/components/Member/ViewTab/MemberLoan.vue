<template>
	<div class = "member-loan">
        <div class = "right-toolbar">
            <button class = "btn btn-app" @click = "printLoanSummary('pdf')"><i class="fa fa-file-pdf-o"></i> Loan Summary</button>
            <!-- <button class = "btn btn-app" @click = "printForm('pdf')"><i class = "fa fa-print"></i> Print</button> -->
        </div>
		<el-table :data="dataAccounts" style="width: 100%" stripe border v-loading = "loadingTable">

            <el-table-column label="Account Number" 
                prop = "account_no">
            </el-table-column>

            <el-table-column label="Loan Type" 
                prop = "product.product_name">
            </el-table-column>

            <el-table-column label="Principal">
                <template slot-scope="scope">
                    <span>{{scope.row.principal}}</span>
                </template>
            </el-table-column>

            <el-table-column label="Balance">
                <template slot-scope="scope">
                    <span>{{scope.row.principal_balance}}</span>
                </template>
            </el-table-column>
            <el-table-column label="Action">
                <template slot-scope="scope">
                    <el-button size="mini" @click="selectAccount(scope.index, scope.row)">View Transaction</el-button>
                </template>
            </el-table-column>

            <!-- <el-table-column label="Maturity Date">
                <template slot-scope="scope">
                    <span>{{ $df.formatDate(scope.row.maturity_date, "MMM D, YYYY") }}</span>
                </template>
            </el-table-column> -->

        </el-table>
        <el-dialog title="Loan Transaction" top = "10px" v-if="dialogVisible"  :visible.sync="dialogVisible" width="80%" @close = "dialogVisible = false">
            <loan-transactions 
                :account-selected = "selectAccountDetails"
                :account-transaction-list = "selectAccountTrans"> 
            </loan-transactions>
        </el-dialog>
	</div>
</template>
<script>

    import cloneDeep from 'lodash/cloneDeep'  
    import _forEach from 'lodash/forEach' 

    import fileExport from '../../../mixins/fileExport'

    import LoanTransactions from '../../Loan/LoanTransactions.vue' 
export default {
    mixins: [fileExport],
	props: ['member', 'canEdit'],
	data: function () {
		return{
			memberData              : this.member,
            dataAccounts            : [],
            loadingTable            : false,
            selectAccountDetails    : {},
            selectAccountTrans      : [],
            dialogVisible           : false
        }
    },
    components: { LoanTransactions },
    created(){
        this.getAccount()
    },
    methods:{
        getAccount(balance){
            this.loadingTable = true

            let type = ['LOAN']
            this.$API.Member.getAccounts(type, this.memberData.id, "")
            .then(result => {
                var res = result.data
                this.dataAccounts = res.loanAccounts
            })
            .catch(err => {
                console.log(err)
            })
            .then(_ => { 
                this.loadingTable = false
            })
        },

        selectAccount(index, data){
            this.selectAccountDetails = cloneDeep(data)
            this.selectAccountDetails['member'] = {}
            this.selectAccountDetails['member']['fullname'] = this.memberData.last_name + " " + this.memberData.first_name + " " + this.memberData.middle_name
            this.getTransaction(this.selectAccountDetails.account_no)
        },
        getTransaction(account_no){
            this.loadingTable = true

            this.$API.Loan.getLoanTransaction(account_no)
            .then(result => {
                var res = result.data
                if(res.length > 0 ){
                    this.selectAccountTrans = res
                }
                this.dialogVisible = true
            })
            .catch(err => {
                console.log(err)
            })
            .then(_ => { 
                this.loadingTable = false
            })
        },
        printLoanSummary(type){
            if(this.dataAccounts.length == 0){
                new Noty({
                    theme: 'relax',
                    type: "error",
                    layout: 'topRight',
                    text: "No loan to export.",
                    timeout: 3000
                }).show();
                return
            }
            let dataLoan = {}
            let dataAccount = {}
            dataAccount['fullname'] = this.memberData.last_name + " "  + this.memberData.first_name + " " + this.memberData.middle_name
            dataAccount['station'] = ""
            if(this.memberData.station){
                dataAccount['station']  = this.memberData.station.name
            }
            let totalPrincipal = 0
            let totalBalance = 0
            _forEach(cloneDeep(this.dataAccounts), rs=>{
                totalPrincipal = parseFloat(totalPrincipal) + parseFloat(rs.principal)
                totalBalance = parseFloat(totalBalance) + parseFloat(rs.principal_balance)
            })
            dataAccount['totalPrincipal'] = totalPrincipal
            dataAccount['totalBalance'] = totalBalance

            dataLoan['details'] = dataAccount
            dataLoan['loanList'] = this.dataAccounts


            this.$API.Loan.printSummary(dataLoan, type)
            .then(result => {
                let res = result.data
                if(type == 'pdf'){
                    this.exporter(type, 'Loan Summary', res)
                }
                else if(type == 'print'){
                    this.winPrint(res.data, 'Loan Summary')
                }
            })
            .catch(err => { console.log(err)})
            .then(_ => { this.pageLoading = false })
        }
    }
}
</script>