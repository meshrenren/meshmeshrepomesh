<template>
	<div class="general-voucher">
        <h3>Create General Voucher</h3>
        <el-alert type="error">
            <template slot = "title">
                <i class="icon fa fa-warning"></i> Note
            </template>
            For <a href = "/savings/withdraw">Savings Deposit Withdrawal</a>, <a href = "/loan/release">Loan Release</a> and <a href = "/tim-deposit/widthdraw">Time Deposit Withdrawal</a>, please process those transaction to their respective links.
        </el-alert>
        <el-row :gutter="40" class = "mt-10">
            <el-col :span="16">
                <voucher-form
                    :data-model = "voucherModel" 
                    :data-details-model = "detailsModel" 
                    :data-particular-list = "particularList"
                    :allow-create-name = 'true'
                    @finishvoucher = "createVoucher">
                </voucher-form>
            </el-col>
            <el-col :span="8">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Savings Account</h3>
                    </div>
                    <div class="box-body">
                        <el-table
                            :data="memberAccounts.savings"
                            border striped
                            style="width: 100%"
                            min-height = "100px"
                            v-loading = "loadingTable">
                            <el-table-column
                                prop="product.description"
                                label="Loan Type">
                                <template slot-scope="scope">
                                    {{ scope.row.product.description}}
                                </template>
                            </el-table-column>
                            <el-table-column
                                prop="balance"
                                label="Balance">
                                 <template slot-scope="scope">
                                    {{ Number(scope.row.balance).toFixed(2) }}
                                </template>                        
                            </el-table-column>
                            <el-table-column
                                prop="account_no"
                                label="Account #">                        
                            </el-table-column>
                        </el-table>
                    </div>
                </div>
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Share Account</h3>
                    </div>
                    <div class="box-body">
                        <el-table
                            :data="memberAccounts.share"
                            border striped
                            style="width: 100%"
                            min-height = "100px"
                            v-loading = "loadingTable">
                            <el-table-column
                                prop="product.name"
                                label="Loan Type">
                                <template slot-scope="scope">
                                    {{ scope.row.product.name}}
                                </template>
                            </el-table-column>
                            <el-table-column
                                prop="balance"
                                label="Balance">
                                 <template slot-scope="scope">
                                    {{ Number(scope.row.balance).toFixed(2) }}
                                </template>                        
                            </el-table-column>
                            <el-table-column
                                prop="accountnumber"
                                label="Account #">                        
                            </el-table-column>
                        </el-table>
                    </div>
                </div>
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Time Deposit Account</h3>
                    </div>
                    <div class="box-body">
                        <el-table
                            :data="memberAccounts.time_deposit"
                            border striped
                            style="width: 100%"
                            min-height = "100px"
                            v-loading = "loadingTable">
                            <el-table-column
                                prop="product.description"
                                label="Loan Type">
                                <template slot-scope="scope">
                                    {{ scope.row.product.description}}
                                </template>
                            </el-table-column>
                            <el-table-column
                                prop="balance"
                                label="Amount">
                                 <template slot-scope="scope">
                                    {{ Number(scope.row.amount).toFixed(2) }}
                                </template>                        
                            </el-table-column>
                            <el-table-column
                                prop="term"
                                label="Term">                        
                            </el-table-column>
                            <el-table-column
                                prop="maturity_date"
                                label="Maturity Date">                        
                            </el-table-column>
                            <el-table-column
                                prop="accountnumber"
                                label="Account #">                        
                            </el-table-column>
                        </el-table>
                    </div>
                </div>
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Loan Account</h3>
                    </div>
                    <div class="box-body">
                        <el-table
                            :data="memberAccounts.loans"
                            border striped
                            style="width: 100%"
                            height = "500px"
                            v-loading = "loadingTable">
                            <el-table-column
                                prop="product.product_name"
                                label="Loan Type"
                                width = "150px">
                                <template slot-scope="scope">
                                    {{ scope.row.product.product_name}}
                                </template>
                            </el-table-column>
                            <el-table-column
                                prop="principal"
                                label="Principal">
                                 <template slot-scope="scope">
                                    {{ Number(scope.row.principal).toFixed(2) }}
                                </template>                        
                            </el-table-column>
                            <el-table-column
                                prop="principal_balance"
                                label="Balance">
                                 <template slot-scope="scope">
                                    {{ Number(scope.row.principal_balance).toFixed(2) }}
                                </template>                        
                            </el-table-column>
                            <el-table-column
                                prop="term"
                                label="Term">                       
                            </el-table-column>
                            <el-table-column
                                prop="account_no"
                                label="Account #">                        
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

    import VoucherForm from './VoucherForm.vue' 

export default {
    components: { VoucherForm },
    props: ['dataModel', 'dataDetailsModel', 'dataParticularList'],
    data: function () {    	

      	return {
            voucherModel            : this.dataModel,
            detailsModel            : this.dataDetailsModel,
            particularList          : this.dataParticularList,
            memberAccounts          : {loans: [], savings : [], share: [], time_deposit : []},
            loadingTable            : false
      	}
  	},
    created(){
        this.$EventDispatcher.listen('CHANGE_NAME', data => {
            this.changeName(data)
        })
    },
    methods:{
        resetAccount(){
            this.memberAccounts.savings = []
            this.memberAccounts.share = []
            this.memberAccounts.time_deposit = []
        },
        changeName(data){
            //get type and type id
            this.resetAccount()
            this.loadingTable = true

            this.$API.Member.getAccounts(data.type, data.id, data.name)
            .then(result => {
                var res = result.data
                this.memberAccounts.loans = res.loanAccounts
                this.memberAccounts.savings = res.savingsAccounts
                this.memberAccounts.share = res.shareAccounts
                this.memberAccounts.time_deposit = res.timedepositAccounts
            })
            .catch(err => {
                console.log(err)
            })
            .then(_ => { 
                this.loadingTable = false
            })
        },
        createVoucher(data){
            let vm = this
            console.log("data", data)
            let voucher = data.data

            let title = 'Save Voucher?'
            let text = "Are you sure you want to save this general voucher?"
            vm.$swal({
                title: title,
                text: text,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: '#d33',
                confirmButtonText: 'Proceed',
                focusConfirm: false,
                focusCancel: true,
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                width: '400px',
            }).then(function(result) {
                if (result.value) {
                    vm.saveVoucherEntries(voucher.voucherModel, voucher.entryList)
                }
            })
        },
        saveVoucherEntries(voucherModel, entryList){
            this.$API.Voucher.saveVoucherEntries(voucherModel, entryList)
            .then(result => {
                var res = result.data
                if(res.success){
                    new Noty({
                        theme: 'relax',
                        type: 'success',
                        layout: 'topRight',
                        text: 'Voucher successfully saved.',
                        timeout: 3000
                    }).show();
                    this.$EventDispatcher.fire('RESET_DATA', [])
                }
                else{
                    let title = "Error: Not Saved"
                    let type = 'warning'
                    let text = "Voucher not successfully saved. Please try again or contact administrator."
                    if(res.error == 'ERROR_HASGV'){
                        title = 'Error: GV Number Exist'
                        text = "GV Number " + voucherModel.gv_num + " already exist."
                        type = "error"
                    }

                    this.getSwalAlert(type, title, text)
                }
            })
            .catch(err => {
                console.log(err)
            })
            .then(_ => { 
                main_preloader.style.display = 'none'
            })
        },
    },
  }
</script>
<style lang="scss">
    
    @import '~noty/src/noty.scss';

</style>
