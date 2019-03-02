<template>
	<div class="general-voucher">
        <h3>Create General Voucher</h3>
        <hr>
        <el-row :gutter="40">
            <el-col :span="16">
                <voucher-form
                    :data-model = "voucherModel" 
                    :data-particular-list = "particularList"
                    @finishvoucher = "createVoucher">
                </voucher-form>
            </el-col>
            <el-col :span="8">
                <h4>Savings Account</h4>
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
                <hr>
                <h4>Share Account</h4>
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
                <hr>
                <h4>Time Deposit Accounr</h4>
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
                <hr>
            </el-col>
        </el-row>
	</div>
</template>

<script>
	window.noty = require('noty');
    import axios from 'axios'
    import Noty from 'noty'  
    import cloneDeep from 'lodash/cloneDeep'    
    import _forEach from 'lodash/forEach'

    import VoucherForm from './VoucherForm.vue' 

export default {
    components: { VoucherForm },
    props: ['dataModel', 'dataParticularList'],
    data: function () {    	

      	return {
      		voucherModel			: this.dataModel,
            particularList          : this.dataParticularList,
            memberAccounts          : {savings : [], share: [], time_deposit : []},
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
        validateEntries(){
            let text = ""
            let type = "error"
            let hasError = false
            if(this.totalDebit != this.totalCredit){
                hasError = true
                text = "Total Credit and Total Debit is not match."
            }
            if(hasError){
                new Noty({
                    theme: 'relax',
                    type: type,
                    layout: 'topRight',
                    text: text,
                    timeout: 3000
                }).show();
            }
            
            return hasError

        },
        createVoucher(data){
            let vm = this
            console.log("data", data)

            let title = 'Add Entries?'
            let text = "Are you sure you want to add entries in general voucher?"
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
                    let generalVoucherList = []
                   /* let list = cloneDeep(vm.entryList)
                    _forEach(list, el=>{
                        el['name'] = vm.getVoucherName(el.name_id)
                        el['description_id'] = el.description_id
                        el['description'] = vm.getDescription(el.description_id)
                        el['date_transact'] = vm.$df.formatDate(el.date_transact, "YYYY-MM-DD")
                        generalVoucherList.push(el)

                    })
                    vm.saveVoucherEntries(generalVoucherList, vm.voucherModel.gv_num)*/
                }
            })
        },
        saveVoucherEntries(generalVoucherList, gvNumber){
            this.$API.Voucher.saveVoucherEntries(generalVoucherList, gvNumber, isForceAdd)
            .then(result => {
                var res = result.data
                if(res.hasError){
                    if(res.error == 'has_gvnum'){
                        this.createVoucher(true)
                    }
                }
                else{
                    new Noty({
                        theme: 'relax',
                        type: 'success',
                        layout: 'topRight',
                        text: 'Voucher entries successfully added.',
                        timeout: 3000
                    }).show();
                    this.resetAll()
                }
                //this.mergeAll(res.member, res.division, res.station)
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
