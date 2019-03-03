<template>
	<div class="general-voucher" v-loading = 'loading'>
        <h3>Create Loan Voucher</h3>
        <hr>
        <el-row :gutter="40">
            <el-col :span="16">
                <voucher-form
                    :data-model = "voucherModel" 
                    :data-details-model = "detailsModel" 
                    :data-particular-list = "particularList"
                    :allow-create-name = 'false'
                    @finishvoucher = "createVoucher">
                </voucher-form>
            </el-col>
            <el-col :span="8">
                <h4>Loan Account</h4>
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

    import VoucherForm from '../Voucher/VoucherForm.vue' 

    import swalAlert from '../../mixins/swalAlert.js' 

export default {
    mixins: [swalAlert],
    components: { VoucherForm },
    props: ['dataModel', 'dataDetailsModel', 'dataParticularList'],
    data: function () {    	

      	return {
      		voucherModel			: this.dataModel,
            detailsModel            : this.dataDetailsModel,
            particularList          : this.dataParticularList,
            memberAccounts          : {loans : []},
            loadingTable            : false,
            loading                 : false
      	}
  	},
    created(){
        this.$EventDispatcher.listen('CHANGE_NAME', data => {
            this.changeName(data)
        })
    },
    methods:{
        resetAccount(){
            this.memberAccounts.loans = []
        },
        changeName(data){
            //get type and type id
            this.resetAccount()
            this.loadingTable = true

            this.$API.Member.getAccounts(data.type, data.id, data.name)
            .then(result => {
                var res = result.data
                this.memberAccounts.loans = res.loanAccounts
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

            let title = 'Save transaction?'
            let text = "Are you sure you want to save loan transaction?"
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
            this.loading = true
            this.$API.Loan.releaseLoanVoucher(voucherModel, entryList)
            .then(result => {
                var res = result.data
                if(res.success){
                    new Noty({
                        theme: 'relax',
                        type: 'success',
                        layout: 'topRight',
                        text: 'Loan Release successfully saved..',
                        timeout: 3000
                    }).show();
                    this.$EventDispatcher.fire('RESET_DATA', [])
                    //this.resetAll()
                }
                else{
                    let title = "Error: Not Saved"
                    let type = 'warning'
                    let text = "Loan Voucher not successfully saved. Please try again or contact administrator."
                    if(res.error == 'ERROR_HASGV'){
                        title = 'Error: GV Number Exist'
                        text = "GV Number " + voucherModel.gv_num + " already exist. Please check in the list and reverse."
                        type = "error"
                    }
                    else if(res.error == 'ERROR_TRANSACTION'){
                        title = 'Error: Loan Transaction'
                        text = "There is an error saving in loan transaction. Please try again or contact administrator."
                        type = "error"
                    }
                    else if(res.error == 'ERROR_GV'){
                        title = 'Error: General Voucher not saved'
                        text = "Loan release transaction successfully posted but General Voucher not saved. Please add General Voucher manually in the link menu 'General Voucher' -> <a href = '/general-voucher/' target = '_blank'>'Create'</a>. Please contact administrator and developer too."
                        type = "error"
                    }

                    this.getSwalAlert(type, title, text)
                }
            })
            .catch(err => {
                console.log(err)
            })
            .then(_ => { 
                this.loading = false
            })
        },
    },
  }
</script>
<style lang="scss">
    
    @import '~noty/src/noty.scss';

</style>
