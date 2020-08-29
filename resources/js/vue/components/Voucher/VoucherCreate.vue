<template>
	<div class="general-voucher" v-loading = "loadingPage">
        <el-alert type="error">
            <template slot = "title">
                <i class="icon fa fa-warning"></i> Note
            </template>
            For <a :href = "$baseUrl+'/savings/withdraw'">Savings Deposit Withdrawal</a>, <a :href = "$baseUrl+'/loan/release'">Loan Release</a> and <a :href = "$baseUrl+'/tim-deposit/widthdraw'">Time Deposit Withdrawal</a>, please process those transaction to their respective links.
        </el-alert>
        <el-row :gutter="40" class = "mt-10">
            <el-col :span="16">
                <el-form :model="voucherModel" :rules="rulesVoucher" ref="voucherForm" label-width="160px" class="demo-ruleForm" label-position = "top">
                    <el-row :gutter="20">
                        <el-col :span="10">
                            <el-form-item label="Name" prop="name_id" ref="name_id">
                                <el-select
                                    @change = "changeName"
                                    v-model="voucherModel.name_id"
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
                                <span><el-radio :disabled = "disableForm" v-model="voucherModel.type" label="Individual">Individual</el-radio></span>
                                <span><el-radio :disabled = "disableForm" v-model="voucherModel.type" label="Group">Group</el-radio></span>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">                
                            <el-form-item label="Date" prop="date_transact" ref="date_transact">
                                <el-date-picker :disabled = "disableForm" v-model="voucherModel.date_transact" type="date" placeholder="Pick a date">                      
                                </el-date-picker>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item label="GV Number" prop="or_num">
                                <el-input :disabled = "disableForm" type="text" v-model="voucherModel.gv_num" ref="or_num"></el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item label="Check Number" prop="check_number">
                                <el-input :disabled = "disableForm" type="text" v-model="voucherModel.check_number" ref="check_number"></el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span="24">
                            <hr>
                        </el-col>
                    </el-row>
                </el-form>
                <el-row :gutter="20" class = "el-row-csm-flex">
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
                                                clearable
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
                                        <el-row :gutter="20">
                                            <el-col :span="12">
                                                <el-form-item label="Debit" prop="debit">
                                                    <el-input type="number" size = "small" :min = "0" v-model="otherModel.debit"></el-input>
                                                </el-form-item>
                                            </el-col>
                                            <el-col :span="12">
                                                <el-form-item label="Credit" prop="credit">
                                                    <el-input type="number" size = "small" :min = "0" v-model="otherModel.credit"></el-input>
                                                </el-form-item>
                                            </el-col>
                                        </el-row>
                                        
                                        
                                        <!-- <el-form-item label="Remarks" prop="remarks">
                                            <el-input type="textarea" :autosize="{ minRows: 2, maxRows: 4}" placeholder="Please input" v-model="textarea2"> </el-input>
                                        </el-form-item> -->
                                    </el-col>
                                </el-row>
                                </el-form>
                            </div>
                            <div class="box-footer clearfix">
                                <el-button class = "auto-width pull-right" size = "mini"  type = "primary" @click = "addOtherAccounts">ADD</el-button>
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
                                              width="90">
                                            </el-table-column>
                                            
                                            <el-table-column
                                              label="Debit"
                                              idth="50">
                                                <template slot-scope="scope" v-if = "scope.row.showDebit">
                                                    <div v-if = "scope.row.type == 'LOAN'">
                                                        <el-input type="number" :min = "0" v-model="scope.row.debit" :disabled = "Number(scope.row.balance) >= 0" @keyup.enter.native = "addAccounts"></el-input>
                                                    </div>
                                                    <div v-else>
                                                        <el-input type="number" :min = "0" v-model="scope.row.debit" @keyup.enter.native = "addAccounts"></el-input>
                                                    </div>
                                                </template>
                                            </el-table-column>

                                            <el-table-column
                                              label="Credit">
                                                <template slot-scope="scope" v-if = "scope.row.showCredit">
                                                    <div v-if = "scope.row.type == 'LOAN'">
                                                        <el-input type="number" :min = "0" v-model="scope.row.credit" :disabled = "Number(scope.row.balance) <= 0" @keyup.enter.native = "addAccounts"></el-input>
                                                    </div>
                                                    <div v-else>
                                                        <el-input type="number" :min = "0" v-model="scope.row.credit" @keyup.enter.native = "addAccounts"></el-input>
                                                    </div>
                                                </template>
                                            </el-table-column>

                                        </el-table>
                                    </el-col>
                                </el-row>
                            </div>
                            <div class="box-footer clearfix">
                                <el-button class = "auto-width pull-right" size = "mini" type = "primary" @click = "addAccounts">ADD</el-button>
                            </div>
                        </div>
                    </el-col>
                </el-row> 
            </el-col>
            <el-col :span="8">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">GENERAL VOUCHER</h3>
                    </div>
                    <div class="box-body payment-entry-list">
                        <el-table
                            class = "mt-10"
                            :data="entryList"
                            border striped
                            style="width: 100%"
                            height = "350px">
                            <el-table-column
                                prop="debit"
                                label="Debit">
                                <template slot-scope="scope">
                                    {{ $nf.formatNumber(scope.row.debit, 2) }}
                                </template>
                            </el-table-column>
                            <el-table-column
                                prop="particular_id"
                                label="Description">
                                <template slot-scope="scope">
                                    {{ scope.row.product_name }}
                                </template>
                            </el-table-column>
                            <el-table-column
                                prop="credit"
                                label="Credit">
                                <template slot-scope="scope">
                                    {{ $nf.formatNumber(scope.row.credit, 2) }}
                                </template>
                            </el-table-column>
                        </el-table>
                    </div>
                </div>
            </el-col>
            <el-col :span="24" class = "mt-10">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">ALL VOUCHER DETAILS</h3>
                        <div class="box-tools pull-right">
                            <!-- <el-button class = "auto-width pull-right ml-5" size = "small" type = "danger" @click = "cancelV()">CANCEL</el-button> -->
                            <el-button class = "auto-width pull-right " size = "small" type = "primary" @click = "createVoucher()">SAVE</el-button>
                            <!-- <el-button class = "auto-width pull-right " size = "small" type = "primary" @click = "showAllList()">SHOW ALL</el-button> -->
                        </div>
                    </div>
                    <div class="box-body payment-entry-list mt-5">
                        <voucher-details-list
                            :page-data = "gvListData"
                            >
                        </voucher-details-list>
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
    import {getNameList} from '../../mixins/getNameList.js'
    import swalAlert from '../../mixins/swalAlert.js'

export default {
    components: { VoucherForm },
    props: ['dataModel', 'dataVoucherList', 'dataDetailsModel', 'dataParticularList'],
    mixins: [getNameList, swalAlert],
    data: function () { 
        let formVoucher  = cloneDeep(this.dataModel)
        formVoucher['name_id'] = null
        formVoucher['member_id'] = null   

        let hasModel = false
        if(this.dataModel.gv_num){
            hasModel = true
        }	

        let accountModel = {key : null, member_id : null, account_no : null, particular_id : null, product_id : null, product_name : "", type : null, balance : 0, debit: 0, credit: 0, showDebit: true, showCredit: true, entry_type : null}
      	return {
            voucherModel            : formVoucher,
            detailsModel            : this.dataDetailsModel,
            particularList          : this.dataParticularList,
            memberAccounts          : {loans: [], savings : [], share: [], time_deposit : []},
            loadingTable            : false,
            rulesVoucher            : {},
            rulesOther              : {},
            accountSelected     : {member_id : null, list : []},
            allTotalAccount     : [],
            disableAccountName  : false,
            accountModel        : accountModel,
            otherModel          : {member_id : null, particular_id : null, debit : 0, credit : 0, entry_type : null},
            loadingPage         : false,
            loading             : false,
            disableForm         : false,
            nameList            : [],
            stationName         : null,
            memberSelectList    : [],
            totalAmount         : {debit : 0, credit : 0},
            hasVoucher          : hasModel,
            voucherDetList      : this.dataVoucherList
      	}
  	},
    created(){
        this.getName()
        this.rulesVoucher = {
            gv_num: [ { required: true, message: 'OR Number cannot be blank.', trigger: 'blur' }
            ],
            name_id: [ { required: true, message: 'Name cannot be blank.', trigger: 'blur' }
            ],
            date_transact: [ { required: true, message: 'Date cannot be blank.', trigger: 'blur' }
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

        this.$EventDispatcher.listen('CHANGE_NAME', data => {
            this.changeName(data)
        })

        if(this.hasVoucher){
            this.setVoucher(this.dataVoucherList)
        }
    },
    methods:{
        setVoucher(List){
            this.allTotalAccount = []
            _forEach(List, rl => {
                let arr = cloneDeep(rl)
                arr.fullname = rl.member ? rl.member.fullname : ""
                arr.product_name = rl.particular ? rl.particular.name : ""
                arr.key = rl.type + "_" + (rl.account_no ? rl.account_no : rl.particular_id)
                if(rl.is_prepaid){
                    arr.key = rl.type + "_PI_" + (rl.account_no ? rl.account_no : rl.particular_id)
                }
                this.allTotalAccount.push(arr)
            })
        },
        getVoucherName(nameId){
            let selectName = this.allNameList.find(rt => {
                return rt.value == nameId
            })
            if(selectName){
                return selectName.label
            }
            return nameId
        },
        getName(){
            this.loadingPage = true
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
                
            this.loadingPage = false
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
        getInAllAccount(key){
            
            let getInd = this.allTotalAccount.findIndex(rs => { return rs.key == key})
            return getInd
        },
        addAccounts(){
            console.log('addAccounts')
            let vm = this
            vm.loadingPage = true

            let isBalance = true
            let checkBalanceText = ""
            _forEach(vm.accountSelected.list, rs =>{
                let acct = cloneDeep(rs)
                if((rs.credit && Number(rs.credit) > 0) || (rs.debit && Number(rs.debit) > 0)){
                    let toAdd = true;
                    if(rs.credit && Number(rs.credit) > 0){
                        if(rs.type == "LOAN" && !rs.is_prepaid){
                            let bal = rs.balance < 0 ? Number(rs.balance) * -1 : rs.balance
                            if(Number(rs.credit) > Number(bal) || Number(rs.debit) > Number(rs.balance)) {
                                isBalance = false
                                toAdd = false
                                checkBalanceText += rs.product_name + ", "
                            }
                        }

                    }
                    if(toAdd){
                        //Check for existing account with same account number
                        let getInd = vm.getInAllAccount(rs.key)
                        if(getInd >= 0){
                            vm.allTotalAccount[getInd].credit = Number(rs.credit)
                            vm.allTotalAccount[getInd].debit = Number(rs.debit)
                        }
                        else{
                            //Push to  allaccount
                            vm.allTotalAccount.push(acct)
                        }
                    }
                    
                }
            })
            vm.loadingPage = false

            if(!this.disableAccountName){ 
                vm.accountSelected.member_id = null
                vm.accountSelected.list = []
            }
            /*vm.$swal({
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
            }).then( result => {
                if (result.value) {
                }
            })*/
            
        },
        getParticular(particular_id){
            return this.dataParticularList.find(rs => { return rs.id == particular_id})
        },
        changeOtherParticular(val){
            let debit = this.getAmount(val, 'DEBIT')
            this.otherModel.debit = debit

            let credit = this.getAmount(val, 'CREDIT')
            this.otherModel.credit = credit
        },
        addOtherAccounts(){
            this.loadingPage = true
            let mdl = cloneDeep(this.otherModel)
            let getParticular = this.getParticular(mdl.particular_id)
            if(getParticular){
                let arr = cloneDeep(this.accountModel)
                if(mdl.member_id){
                    arr.member_id = mdl.member_id
                    arr.fullname = this.getMemberName(mdl.member_id)
                }else{
                    arr.fullname = this.voucherModel.name_id
                }

                let key = "OTHERS_" + mdl.particular_id
                let getInd = this.allTotalAccount.findIndex(rs => { return rs.key == key && rs.member_id == arr.member_id})
                if(getInd >= 0){
                    this.allTotalAccount[getInd].credit = Number(mdl.credit)
                    this.allTotalAccount[getInd].debit = Number(mdl.debit)
                }
                else{
                    arr.key = "OTHERS_" + mdl.particular_id
                    arr.account_no = null
                    arr.product_id = mdl.particular_id // Particular
                    arr.particular_id = mdl.particular_id
                    arr.product_name = getParticular.name
                    arr.type = "OTHERS"
                    arr.debit = Number(mdl.debit)
                    arr.credit = Number(mdl.credit)
                    
                    this.allTotalAccount.push(arr)
                }
            }  

            this.otherModel.particular_id = null
            this.otherModel.amount = 0

            this.loadingPage = false          
        },
        resetAccount(){
            this.memberAccounts.savings = []
            this.memberAccounts.share = []
            this.memberAccounts.time_deposit = []
        },
        resetAccounts(){
            this.disableAccountName = false
            this.$refs.otherModel.resetFields()
            this.accountSelected.member_id = null
            this.accountSelected.list = []
        },
        getMemberName(member_id){
            let member = this.memberList.find(rs => {return String(rs.id) == String(member_id)})
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
        changeName(val){
            let nameSplit = val
            nameSplit = nameSplit.split("-")
            this.voucherModel.type = "Group"
            this.voucherModel.member_id = null
            this.stationName = ""
            if(nameSplit.length > 1){
                if(nameSplit[0] == 'member'){
                    this.voucherModel.type = "Individual"
                    this.voucherModel.member_id = nameSplit[1]
                    let getName = this.getPaymentName(val)
                    console.log('getName', getName)
                    if(getName && getName.station_name){
                        this.stationName = getName.station_name
                    }
                }
            }

            this.resetAccounts()

            if(this.voucherModel.member_id){
                this.memberSelectList = cloneDeep(this.memberList)

                this.otherModel.member_id = this.voucherModel.member_id
                this.accountSelected.member_id = this.voucherModel.member_id
                this.disableAccountName = true 
                this.getMemberAccount(this.voucherModel.member_id)
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

                //Set other
                this.otherModel.member_id = member_id
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
                    arr.particular_id = rs.product.particular_id

                    let credit = this.getAmount(arr.key, 'CREDIT')
                    arr.credit = credit
                    arr.showDebit = false

                    allAccounts.push(arr)
                })
            }

            let savingsData = null
            if(savings && savings.length > 0){
                _forEach(savings, rs =>{
                    savingsData = rs

                    let arr = cloneDeep(this.accountModel)
                    arr.member_id = rs.member_id
                    arr.fullname = this.getMemberName(rs.member_id)
                    arr.key = "SAVINGS_" + rs.account_no
                    arr.account_no = rs.account_no
                    arr.product_id = rs.saving_product_id
                    arr.product_name = rs.product.description
                    arr.type = "SAVINGS"
                    arr.balance = parseFloat(rs.balance).toFixed(2)
                    arr.particular_id = rs.product.particular_id

                    let credit = this.getAmount(arr.key, 'CREDIT')
                    arr.credit = credit
                    arr.showDebit = false

                    allAccounts.push(arr)
                })
            }

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
                    arr.particular_id = rs.product.particular_id

                    if(arr.balance > 0){
                        let credit = this.getAmount(arr.key, 'CREDIT')
                        arr.credit = credit
                    }
                    else{
                        arr.showFalse = false
                        arr.credit = 0
                    }

                    if(arr.balance < 0){
                        let debit = this.getAmount(arr.key, 'DEBIT')
                        arr.debit = debit
                    }
                    else{
                        arr.showDebit = false
                        arr.debit = 0
                    }

                    arr.add_as_savings = null
                    arr.savings_data = savingsData

                    allAccounts.push(arr)


                    //Add PI for Loan that has pi particular field
                    if(Number(rs.product.pi_particular_id) > 0){
                        let arr = cloneDeep(this.accountModel)
                        arr.member_id = rs.member_id
                        arr.fullname = this.getMemberName(rs.member_id)
                        arr.key = "LOAN_PI_" + rs.account_no
                        arr.account_no = rs.account_no
                        arr.product_id = rs.loan_id
                        arr.product_name = "PI " + rs.product.product_name
                        arr.type = "LOAN"
                        arr.balance = parseFloat(rs.principal_balance).toFixed(2)
                        arr.particular_id = rs.product.pi_particular_id
                        arr.is_prepaid = true

                        let credit = this.getAmount(arr.key, 'CREDIT')
                        arr.credit = credit
                        arr.showDebit = false

                        allAccounts.push(arr)
                    }
                })
            }
            this.accountSelected.list = allAccounts
            
        },
        getAmount(key, type){
            let getInd = this.allTotalAccount.findIndex(rs => { return rs.key == key})
            if(getInd >= 0){
                if(type == 'CREDIT'){
                   return Number(this.allTotalAccount[getInd].credit)
                }
                else if(type == 'DEBIT'){
                   return Number(this.allTotalAccount[getInd].debit)
                }
            }
            return null
        },
        createVoucher(){
            let vm = this

            if(this.totalAmount.debit != this.totalAmount.credit){
                new Noty({
                    theme: 'relax',
                    type: 'error',
                    layout: 'topRight',
                    text: 'Debit and Credit is not Balance.',
                    timeout: 3000
                }).show();

                return
            }

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
            }).then( result => {
                if (result.value) {
                    let vModel = cloneDeep(this.voucherModel)
                    vModel.date_transact = this.$df.formatDate(vModel.date_transact, 'YYYY-MM-DD')
                    vModel.name = vm.getVoucherName(vModel.name_id)
                    vm.saveVoucherEntries(vModel, this.allTotalAccount, this.entryList )
                }
            })
        },
        saveVoucherEntries(voucherModel, allAccounts, entryList){
            this.loadingPage = true
            this.$API.Voucher.saveVoucherEntries(voucherModel, allAccounts, entryList)
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
                    this.resetAll()
                    //this.$EventDispatcher.fire('RESET_DATA', [])
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
                this.loadingPage = false
            })
        },
        resetAll(){
            this.$refs.voucherForm.resetFields()
            this.resetAccounts()
            this.allTotalAccount = []
        }
    },
    computed:{
        entryList(){
            let list = cloneDeep(this.allTotalAccount)
            let account = []
            let totalAmt = 0

            let tCre = 0
            let tDeb = 0
            _forEach(list, rs =>{
                let acct = rs
                acct['table_key'] = rs.type + "_" + rs.particular_id
                let getInd = -1

                getInd = account.findIndex(ac => { return ac.particular_id == rs.particular_id})

                if(getInd >= 0){
                    let dbt = cloneDeep(Number(account[getInd].debit)) + Number(acct.debit)
                    account[getInd].debit = dbt

                    let crd = cloneDeep(Number(account[getInd].credit)) + Number(acct.credit)
                    account[getInd].credit = crd
                }
                else{
                    account.push(acct)
                }

                tCre += acct.credit ? Number(acct.credit) : 0
                tDeb += acct.debit ? Number(acct.debit) : 0
            })

            this.totalAmount.debit = tDeb
            this.totalAmount.credit = tCre

            return account
        },
        gvListData(){
            let totalAcc = this.entryList
            let allTotalAcc  = this.allTotalAccount
            return {
                entryList : totalAcc,
                allTotalAccount  : allTotalAcc
            }
        }
    }
  }
</script>
<style lang="scss">
    
    @import '~noty/src/noty.scss';
    .general-voucher{
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
