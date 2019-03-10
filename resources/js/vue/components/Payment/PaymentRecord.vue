<template>
	<div class="payment-record" v-loading = "loadingPage">
		<el-row :gutter="30">
            <el-col :span="16">
                <el-form :model="paymentModel" :rules="rulesPayment" ref="paymentForm" label-width="160px" class="demo-ruleForm" label-position = "top">
                    <el-row :gutter="20">
                        <el-col :span="10">
                            <el-form-item label="Name" prop="name_id" ref="name_id">
                                <el-select
                                    @change = "changeName"
                                    v-model="paymentModel.name_id"
                                    filterable
                                    remote allow-create default-first-option
                                    reserve-keyword
                                    placeholder="Please enter name"
                                    :remote-method="remoteMethod"
                                    :loading="loading"
                                    :disabled = "disableForm">
                                    <el-option
                                      v-for="item in nameList"
                                      :key="item.value"
                                      :label="item.label"
                                      :value="item.value">
                                    </el-option>
                                </el-select>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item label="Station">
                                <el-input type="text" :disabled = "true" v-model="stationName"></el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span = "6">
                            <el-form-item label=" " prop="type" ref="type">
                                <span><el-radio :disabled = "disableForm" v-model="paymentModel.type" label="Individual">Individual</el-radio></span>
                                <span><el-radio :disabled = "disableForm" v-model="paymentModel.type" label="Group">Group</el-radio></span>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">                
                            <el-form-item label="Date" prop="date_transact" ref="date_transact">
                                <el-date-picker :disabled = "disableForm" v-model="paymentModel.date_transact" type="date" placeholder="Pick a date">                      
                                </el-date-picker>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item label="OR Number" prop="or_num">
                                <el-input :disabled = "disableForm" type="text" v-model="paymentModel.or_num" ref="or_num"></el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item label="Check Number" prop="check_number">
                                <el-input :disabled = "disableForm" type="text" v-model="paymentModel.check_number" ref="check_number"></el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span="24">
                            <hr>
                        </el-col>
                    </el-row>
                </el-form>
                <el-row :gutter="20">
                    <el-col :span="8">
                        <div class="box box-default">
                            <div class="box-header with-border">
                                <h3 class="box-title">Others</h3>
                            </div>
                            <div class="box-body">
                                <el-form :model="otherModel" :rules="rulesOther"  ref="otherModel" label-width="100px" label-position = "top">
                                <el-row :gutter="20">
                                    <el-col :span="24">
                                        <el-form-item label="Name" prop="member_id">
                                            <el-select
                                                v-model="otherModel.member_id"
                                                filterable
                                                remote :allow-create = "false"
                                                reserve-keyword
                                                placeholder="Select member"
                                                :remote-method="memberRemoteMethod"
                                                :disabled = "disableAccountName"
                                                >
                                                <el-option
                                                    v-for="item in memberSelectList"
                                                    :key="item.id"
                                                    :label="item.first_name + ' ' + item.middle_name + ' ' + item.last_name"
                                                    :value="item.id">
                                                </el-option>
                                            </el-select>
                                        </el-form-item>
                                        <el-form-item label="Particular" prop="particular_id">
                                            <el-select v-model="otherModel.particular_id" filterable placeholder="Select" @change = "changeOtherParticular"  ref="particular_id">
                                                <el-option
                                                    v-for="item in dataParticularList"
                                                    :key="parseInt(item.id)"
                                                    :label="item.name"
                                                    :value="parseInt(item.id)">
                                                </el-option>
                                            </el-select>
                                        </el-form-item>
                                    </el-col>

                                    <el-col :span="24">
                                        <el-form-item label="Amount" prop="amount">
                                            <el-input type="number" size = "small" :min = "0" v-model="otherModel.amount"></el-input>
                                        </el-form-item>
                                        <el-form-item label=" " prop="entry_type">
                                            <el-radio v-model="otherModel.entry_type" label="CREDIT">CREDIT</el-radio>
                                            <el-radio v-model="otherModel.entry_type" label="DEBIT">DEBIT</el-radio>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                                </el-form>
                            </div>
                            <div class="box-footer clearfix">
                                <el-button class = "auto-width pull-right" :size = "mini"  type = "primary" @click = "addOtherAccounts">ADD</el-button>
                            </div>
                        </div>
                    </el-col>
                    <el-col :span="16">
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title">Accounts</h3>
                            </div>
                            <div class="box-body">
                                <el-row :gutter="20">
                                    <el-col :span="24" class = "mb-10">
                                        <label>Account Name: </label>
                                        <el-select
                                            class = "w300-width"
                                            @change = "changeAccountName"
                                            v-model="accountSelected.member_id"
                                            filterable
                                            remote :allow-create = "false"
                                            reserve-keyword
                                            placeholder="Select member"
                                            :remote-method="memberRemoteMethod"
                                            :disabled = "disableAccountName"
                                            >
                                            <el-option
                                              v-for="item in memberSelectList"
                                              :key="item.id"
                                              :label="item.first_name + ' ' + item.middle_name + ' ' + item.last_name"
                                              :value="item.id">
                                            </el-option>
                                        </el-select>
                                    </el-col>

                                    <el-col :span="24">
                                        <el-table
                                            :data="accountSelected.list"
                                            border
                                            style="width: 100%"
                                            height = "230">
                                            <el-table-column
                                              prop="product_name"
                                              label="Account Type"
                                              width="180">
                                            </el-table-column>
                                            <el-table-column
                                              prop="balance"
                                              label="Balance"
                                              width="150">
                                            </el-table-column>

                                            <el-table-column
                                              label="Amount">
                                                <template slot-scope="scope">
                                                    <el-input type="number" :min = "0" v-model="scope.row.amount" :disabled = "Number(scope.row.balance) <= 0" @keyup.enter.native = "addEntry"></el-input>
                                                </template>
                                            </el-table-column>
                                        </el-table>
                                    </el-col>
                                </el-row>
                            </div>
                            <div class="box-footer clearfix">
                                <el-button class = "auto-width pull-right" :size = "mini" type = "primary" @click = "addAccounts">ADD</el-button>
                            </div>
                        </div>
                    </el-col>
                </el-row> 
            </el-col>
            <el-col :span="8">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">TOTAL</h3>
                    </div>
                    <div class="box-body psyment-entry-list">
                        <el-table
                            :data="totalAccounts"
                            border striped
                            style="width: 100%"
                            height = "480px">
                            <el-table-column
                                prop="product_name"
                                label="Account">                            
                            </el-table-column>
                            <el-table-column
                                prop="type"
                                label="Type"> 
                            </el-table-column>
                            <el-table-column
                                prop="amount"
                                label="Amount">
                            </el-table-column>
                        </el-table>
                    </div>
                </div>
            </el-col>
            <el-col :span="24">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">ALL ACCOUNTS</h3>
                        <div class="box-tools pull-right">
                            <el-button class = "auto-width pull-right ml-5" size = "small" type = "danger" @click = "cancelPayment()">CANCEL</el-button>
                            <el-button class = "auto-width pull-right " size = "small" type = "primary" @click = "finishPayment()">SAVE</el-button>
                        </div>
                    </div>
                    <div class="box-body payment-entry-list mt-5">
                        <el-table
                            :data="allTotalAccount.filter(data => !nameSearch || data.fullname.toLowerCase().includes(nameSearch.toLowerCase()))"
                            border striped
                            style="width: 100%"
                            max-height = "480px">
                            <el-table-column prop = "fullname"> 
                                <template slot="header" slot-scope="scope">
                                    <el-input v-model="nameSearch" size="mini" placeholder="Search Member"/>
                                </template>                       
                            </el-table-column>
                            <el-table-column
                                prop="product_name"
                                label="Account">                            
                            </el-table-column>
                            <el-table-column
                                prop="amount"
                                label="Amount">
                            </el-table-column>
                            <el-table-column
                                prop="type"
                                label="Type"> 
                            </el-table-column>
                        </el-table>
                    </div>
                </div>
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

    import {getNameList} from '../../mixins/getNameList.js'
    import swalAlert from '../../mixins/swalAlert.js'

export default {
    mixins: [getNameList, swalAlert],
    props: ['dataModel', 'dataParticularList'],
    data: function () {  
        let formPayment  = {}
        this.dataModel.forEach(function(detail){
            formPayment[detail] = null
        })
        formPayment['name_id'] = null
        formPayment['member_id'] = null

        let accountModel = {key : null, member_id : null, account_no : null, particular_id : null, product_id : null, product_name : "", type : null, balance : 0, amount: 0, entry_type : null}
    	return {
            paymentModel        : formPayment,
            disableForm         : false,
            rulesPayment        : {},
            nameList		    : [],
            totalAmount         : 0,
            stationName         : '',
            loading             : false,
            entryList           : [],
            accountSelected     : {member_id : null, list : []},
            allTotalAccount     : [],
            disableAccountName  : false,
            accountModel        : accountModel,
            otherModel          : {member_id : null, particular_id : null, amount : 0, entry_type : null},
            rulesOther          : {},
            loadingPage         : false,
            memberSelectList    : [],
            nameSearch          : '',
    	}
    },
    created(){

        this.getName()

        this.rulesPayment = {
            or_num: [ { required: true, message: 'OR Number cannot be blank.', trigger: 'blur' }
            ],
            name_id: [ { required: true, message: 'Name cannot be blank.', trigger: 'blur' }
            ],
            date_transact: [ { required: true, message: 'Date cannot be blank.', trigger: 'blur' }
            ], 
            particular_id: [ { required: true, message: 'Description cannot be blank.', trigger: 'blur' }
            ],   
            amount_paid: [ { required: true, message: 'Amount cannot be blank.', trigger: 'blur' }
            ],            
        } 

        this.rulesOther = {
            particular_id: [ { required: true, message: 'Particular cannot be blank.', trigger: 'blur' }
            ],
            amount: [ { required: true, message: 'Amount cannot be blank.', trigger: 'blur' }
            ],
            entry_type: [ { required: true, message: 'Please select entry type.', trigger: 'blur' }
            ],
        }
    },
    computed:{
        totalAccounts(){
            let list = this.allTotalAccount
            let account = []

            _forEach(list, rs =>{
                let acct = cloneDeep(rs)

                let getInd = account.findIndex(rs => { return rs.type == acct.type && rs.product_id == acct.product_id})
                if(getInd >= 0){
                    let amt = cloneDeep(Number(account[getInd].amount)) + Number(acct.amount)
                    account[getInd].amount = amt
                }
                else{
                    account.push(acct)
                }
            })

            _forEach(account, rs =>{
                rs.amount = Number(rs.amount).toFixed(2)
            })

            return account 
        }
    },
    methods: {
        getMemberName(member_id){
            let member = this.memberList.find(rs => {return String(rs.id) == String(member_id)})
            console.log("member", member)
            if(member){
                return member.first_name + ' ' + member.middle_name + ' ' + member.last_name
            }
            else{
                return member_id
            }

        },  
        getPaymentName(nameId){
            let selectName = this.allNameList.find(rt => {
                return rt.value == nameId
            })
            if(selectName){
                return selectName.label
            }
            return nameId
        },
        getDescription(descId){
            let selectDesc = this.dataParticularList.find(rt => {
                return rt.id == descId
            })
            if(selectDesc){
                return selectDesc.name
            }
            return "" 
        },
        getName(){
            this.$API.Voucher.getVoucherName()
            .then(result => {
                var res = result.data
                this.memberList = res.member
                this.stationList = res.station
                this.mergeAll(res.member)
            })
            .catch(err => {
                console.log(err)
            })
            .then(_ => { 
                main_preloader.style.display = 'none'
            })
        
        },
        remoteMethod(query){
            if (query && query !== '') {
                this.loading = true;
                setTimeout(() => {
                    this.loading = false;
                    this.nameList = this.allNameList.filter(item => {
                        return item.label.toLowerCase().indexOf(query.toLowerCase()) > -1;
                    });
                }, 200);
            } else {
              this.nameList = [];
            }
        },
        memberRemoteMethod(query){
            if (query && query !== '') {
                setTimeout(() => {
                    this.memberSelectList = this.memberList.filter(item => {
                        let fullname = item.first_name + " " + item.middle_name + " " + item.last_name
                        return fullname.toLowerCase().indexOf(query.toLowerCase()) > -1;
                    });
                }, 200);
            } else {
              this.memberSelectList = [];
            }
        },
        resetAccounts(){
            this.disableAccountName = false
            this.$refs.otherModel.resetFields()
            this.accountSelected.member_id = null
            this.accountSelected.list = []
        },
        changeName(val){
            //get type and type id
            let nameSplit = val
            nameSplit = nameSplit.split("-")
            this.paymentModel.type = "Group"
            this.paymentModel.member_id = null
            this.stationName = ""
            if(nameSplit.length > 1){
                if(nameSplit[0] == 'member'){
                    this.paymentModel.type = "Individual"
                    this.paymentModel.member_id = nameSplit[1]
                    let getName = this.getPaymentName(val)
                    if(getName && getName.station_name){
                        this.stationName = getName.station_name
                    }
                }
            }

            this.resetAccounts()

            if(this.paymentModel.member_id){
                this.memberSelectList = cloneDeep(this.memberList)

                this.otherModel.member_id = this.paymentModel.member_id
                this.accountSelected.member_id = this.paymentModel.member_id
                this.disableAccountName = true 
                this.getMemberAccount(this.paymentModel.member_id)
            }

        },
        changeAccountName(val){
            this.getMemberAccount(val)
        },
        getMemberAccount(member_id){
            this.loadingPage = true
            this.$API.Payment.getMemberAccount(member_id)
            .then(result => {
                let res = result.data
                this.mergeAccount(res.loanAccounts, res.savingsAccounts, res.shareAccounts)
            })
            .catch(err => {
                console.log(err)
            })
            .then(_ => { 
                this.loadingPage = false
            })
        },
        mergeAccount(loans = null, savings = null, shares = null){
            let allAccounts = []
            if(loans && loans.length > 0){
                _forEach(loans, rs =>{

                    let arr = cloneDeep(this.accountModel)
                    arr.member_id = rs.member_id
                    arr.fullname = this.getMemberName(rs.member_id)
                    arr.key = "LOAN_" + rs.account_no
                    arr.account_no = rs.account_no
                    arr.product_id = rs.loan_id
                    arr.product_name = rs.product.product_name
                    arr.type = "LOAN"
                    arr.balance = parseFloat(rs.principal_balance).toFixed(2)

                    let amount = this.getAmount(arr.key)
                    arr.amount = amount

                    allAccounts.push(arr)
                })
            }

            if(savings && savings.length > 0){
                _forEach(savings, rs =>{

                    let arr = cloneDeep(this.accountModel)
                    arr.member_id = rs.member_id
                    arr.fullname = this.getMemberName(rs.member_id)
                    arr.key = "SAVINGS_" + rs.account_no
                    arr.account_no = rs.account_no
                    arr.product_id = rs.saving_product_id
                    arr.product_name = rs.product.description
                    arr.type = "SAVINGS"
                    arr.balance = parseFloat(rs.balance).toFixed(2)

                    let amount = this.getAmount(arr.key)
                    arr.amount = amount

                    allAccounts.push(arr)
                })
            }

            if(shares && shares.length > 0){
                _forEach(shares, rs =>{

                    let arr = cloneDeep(this.accountModel)
                    arr.member_id = rs.fk_memid
                    arr.fullname = this.getMemberName(rs.fk_memid)
                    arr.key = "SHARE_" + rs.accountnumber
                    arr.account_no = rs.accountnumber
                    arr.product_id = rs.fk_share_product
                    arr.product_name = rs.product.name
                    arr.type = "SHARE"
                    arr.balance = parseFloat(rs.balance).toFixed(2)

                    let amount = this.getAmount(arr.key)
                    arr.amount = amount

                    allAccounts.push(arr)
                })
            }

            this.accountSelected.list = allAccounts
            
        },
        getAmount(key){
            let getInd = this.allTotalAccount.findIndex(rs => { return rs.key == key})
            if(getInd >= 0){
                return Number(this.allTotalAccount[getInd].amount)
            }
            return null
        },
        getInAllAccount(key){
            let getInd = this.allTotalAccount.findIndex(rs => { return rs.key == key})
            return getInd
        },
        addAccounts(){
            let vm = this
             vm.$swal({
                title: "Add Accounts",
                text: "Are you sure you want to add accounts listed?",
                type: 'question',
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
                    vm.loadingPage = true
                    _forEach(vm.accountSelected.list, rs =>{
                        let acct = cloneDeep(rs)
                        if(rs.amount && Number(rs.amount) > 0){
                            //Check for existing account with same account number
                            let getInd = vm.getInAllAccount(rs.key)
                            if(getInd >= 0){
                                vm.allTotalAccount[getInd].amount = Number(rs.amount)
                            }
                            else{
                                //Push to  allaccount
                                vm.allTotalAccount.push(acct)
                            }
                        }
                    })
                    vm.loadingPage = false
                    vm.accountSelected.member_id = null
                    vm.accountSelected.list = []
                }
            })
            
        },
        getParticular(particular_id){
            return this.dataParticularList.find(rs => { return rs.id == particular_id})
        },
        changeOtherParticular(val){
            let amount = this.getAmount(val)
            this.otherModel.amount = amount
        },
        addOtherAccounts(){
            this.loadingPage = true
            let mdl = cloneDeep(this.otherModel)
            let getParticular = this.getParticular(mdl.particular_id)
            if(getParticular){
                let arr = cloneDeep(this.accountModel)
                arr.key = "OTHERS_" + mdl.particular_id
                arr.account_no = null
                arr.product_id = mdl.particular_id // Particular
                arr.particular_id = mdl.particular_id
                arr.product_name = getParticular.name
                arr.type = "OTHERS"
                arr.amount = Number(mdl.amount)
                if(mdl.member_id){
                    arr.member_id = mdl.member_id
                    arr.fullname = this.getMemberName(mdl.member_id)
                }else{
                    arr.fullname = this.paymentModel.name_id
                }

                this.allTotalAccount.push(arr)
            }  

            this.otherModel.particular_id = null
            this.otherModel.amount = 0

            this.loadingPage = false          
        },
        handleRemove(index, row){
            this.entryList.splice(index, 1)
        },
        validatePayment(){
            let text = ""
            let type = "error"
            let hasError = false

            this.$refs['paymentForm'].validate((valid) => {
                if (valid) {
                }
                else{
                    hasError = true
                    text = "Please complete Payment Details."
                }
            });

            if(!hasError){
                if(this.allTotalAccount.length <= 0){
                    hasError = true
                    text = "Please add accounts to pay."
                }
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
        finishPayment(){
            let vm = this

            if(this.validatePayment()){
                return;
            }
            let title = 'Save Payment?'
            let text = "Are you sure you want to save payment list?"
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
                    let model = cloneDeep(vm.paymentModel)
                    model.date_transact = vm.$df.formatDate(model.date_transact, "YYYY-MM-DD")
                    model.name = vm.getPaymentName(model.name_id)

                    vm.savePaymentList(model, vm.allTotalAccount)
                }
            })
        },
        savePaymentList(paymentModel, allAccounts){
            this.$API.Payment.savePaymentList(paymentModel, allAccounts)
            .then(result => {
                var res = result.data
                if(res.success){
                    new Noty({
                        theme: 'relax',
                        type: 'success',
                        layout: 'topRight',
                        text: 'Payment list successfully saved.',
                        timeout: 3000
                    }).show();
                    this.resetAll()
                }
                else{

                    let title = "Error: Not Saved"
                    let type = 'warning'
                    let text = "Payments not successfully saved. Please try again or contact administrator."
                    if(res.error == 'ERROR_HASOR'){
                        title = 'Error: OR Number Exist'
                        text = "OR Number " + paymentModel.or_num + " already exist."
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
        cancelPayment(){
            let vm = this
            vm.$swal({
                title: 'Cancel All Data?',
                text: "Are you sure you want to cancel all data.",
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
                    vm.resetAll()

                }
            })
        },
        resetAll(){
            this.$refs.paymentForm.resetFields()
            this.resetAccounts()
            this.allTotalAccount = []
        }
    },
    watch:{
        'entryList': function(val){
            let total = 0
            if(val.length > 0){
                _forEach(val, vl =>{
                    if(vl.amount){
                        total = parseFloat(total) + parseFloat(vl.amount)
                    }
                })

                total = total.toFixed(2)
            }

            this.totalAmount = total
        }
    }
}
</script>
<style lang="scss">
    
    @import '~noty/src/noty.scss';
    .payment-record{
        .el-form{
            .el-button{
                width: 100%;
            }
        }
        .el-form--label-top{
            .el-form-item{
                .el-form-item__label{
                    line-height: 10px;
                    padding: 0 0 0px;
                }
            }
        }
        

        .el-select{
            width: 100%;
        }

        .el-date-editor{
            width: 100%;
        }


        .el-button{
            width: 100%;
        }

        hr{
            border-top: 1px solid #d6d6d6;
            margin-top: 10px;
            margin-bottom: 10px;
        }
    }

	
</style>