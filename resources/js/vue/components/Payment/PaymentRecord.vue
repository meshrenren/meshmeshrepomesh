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
                                        <el-form-item label="Amount" prop="amount">
                                            <el-input type="number" size = "small" :min = "0" v-model="otherModel.amount"></el-input>
                                        </el-form-item>
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
                                              label="Amount">
                                                <template slot-scope="scope">
                                                    <div v-if = "scope.row.type == 'LOAN'">
                                                        <el-input type="number" :min = "0" v-model="scope.row.amount" :disabled = "Number(scope.row.balance) <= 0" @keyup.enter.native = "addAccounts"></el-input>
                                                    </div>
                                                    <div v-else>
                                                        <el-input type="number" :min = "0" v-model="scope.row.amount" @keyup.enter.native = "addAccounts"></el-input>
                                                    </div>
                                                </template>
                                            </el-table-column>

                                            <el-table-column
                                              label="Add as Savings">
                                                <template slot-scope="scope" v-if= "scope.row.type == 'LOAN' && !scope.row.is_prepaid">
                                                    <el-input type="number" :min = "0" v-model="scope.row.add_as_savings" :disabled = "Number(scope.row.balance) > 0" @keyup.enter.native = "addAccounts"></el-input>
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
                        <h3 class="box-title">TOTAL PAYMENT</h3>
                    </div>
                    <div class="box-body payment-entry-list">
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
                                <template slot-scope="scope">
                                    {{ $nf.formatNumber(scope.row.principal, 2) }}
                                </template>
                            </el-table-column>
                        </el-table>
                    </div>
                </div>
            </el-col>
            <el-col :span="24" class = "mt-10">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">ALL ACCOUNTS</h3>
                        <div class="box-tools pull-right">
                            <el-button class = "auto-width pull-right ml-5" size = "small" type = "danger" @click = "cancelPayment()">CANCEL</el-button>
                            <el-button class = "auto-width pull-right " size = "small" type = "primary" @click = "finishPayment()">SAVE</el-button>
                            <!-- <el-button class = "auto-width pull-right " size = "small" type = "primary" @click = "showAllList()">SHOW ALL</el-button> -->
                        </div>
                    </div>
                    <div class="box-body payment-entry-list mt-5">
                        <payment-record-list
                            :page-data = "pytRecListData"
                            >
                        </payment-record-list>
                    </div>
                </div>
            </el-col>
        </el-row> 
       <!--  <dialog-modal 
            title-header = ""
            width = "95%"
            v-if="showListModal"
            :visible.sync="showListModal"
            @close="showListModal = false">
            <payment-record-list
                :page-data = "pytRecListData"
                >
            </payment-record-list>
        </dialog-modal> -->       
	</div>
</template>

<script>
	window.noty = require('noty')
    import Noty from 'noty'  
    import cloneDeep from 'lodash/cloneDeep'    
    import _forEach from 'lodash/forEach'
    import _reduce from 'lodash/reduce'

    import {getNameList} from '../../mixins/getNameList.js'
    import swalAlert from '../../mixins/swalAlert.js'

export default {
    mixins: [getNameList, swalAlert],
    props: ['dataModel', 'dataPaymentList', 'dataParticularList', 'dataPaymentRecord'],
    data: function () {  
        let formPayment  = cloneDeep(this.dataModel)
        formPayment['name_id'] = null
        formPayment['member_id'] = null

        let hasModel = false
        if(this.dataModel.or_num){
            hasModel = true
        }

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
            showListModal       : false,
            hasPayment           : hasModel,
            paymentRecordList   : this.dataPaymentRecord
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
        } 

        this.rulesOther = {
            particular_id: [ { required: true, message: 'Particular cannot be blank.', trigger: 'blur' }
            ],
            amount: [ { required: true, message: 'Amount cannot be blank.', trigger: 'blur' }
            ],
            entry_type: [ { required: true, message: 'Please select entry type.', trigger: 'blur' }
            ],
        }

        if(this.hasPayment){
            this.setPayment(this.paymentRecordList)
        }
    },
    computed:{
        totalAccounts(){
            let list = this.allTotalAccount
            let account = []
            let totalAmt = 0

            _forEach(list, rs =>{
                let acct = cloneDeep(rs)
                acct['table_key'] = rs.type + "_" + rs.product_id
                let getInd = -1

                if(rs.is_prepaid !== undefined && rs.is_prepaid){
                    acct['table_key'] = rs.type + "_PI" + "_" + rs.product_id
                    getInd = account.findIndex(ac => { return ac.is_prepaid !== undefined && ac.is_prepaid && ac.type == acct.type && ac.product_id == acct.product_id})
                }else{
                    getInd = account.findIndex(ac => { return ac.type == acct.type && ac.product_id == acct.product_id})
                }

                if(getInd >= 0){
                    let amt = cloneDeep(Number(account[getInd].amount)) + Number(acct.amount)
                    account[getInd].amount = amt
                }
                else{
                    account.push(acct)
                }
            })



            _forEach(account, rs =>{
                let accAmount = parseFloat(rs.amount).toFixed(2)
                rs.amount = accAmount
                totalAmt = parseFloat(totalAmt) + parseFloat(accAmount)
            })

            this.paymentModel.amount_paid = totalAmt
            this.totalAmount = totalAmt

            return account 
        },
        accMemList(){
            let totalAccnt = cloneDeep(this.allTotalAccount)
            let arrMem = []
            if(totalAccnt.length > 0){
                arrMem = Array.from(new Set(totalAccnt.map(t => { return t.member_id})))
                arrMem = arrMem.map(id => {
                    let getMem = totalAccnt.find(s => { return s.member_id == id})
                    return { id : id, fullname : getMem.fullname}
                })
            }
            
            return arrMem
        },
        allAccountList(){
            let allTotals = cloneDeep(this.allTotalAccount)
            let accMem = cloneDeep(this.accMemList)
            let accTotal = cloneDeep(this.totalAccounts)
            let memRows = []

            _forEach(accMem, mem => {
                let arr = mem
                let sumTotal = 0
                _forEach(accTotal, acc => {
                    console.log("arr", arr)
                    let getAccAmount = allTotals.filter(rs => { return rs.type == acc.type && rs.product_id == acc.product_id && mem.id == rs.member_id})
                    let sumAcc = _reduce(getAccAmount, function(result, val) {
                      return result + parseFloat(val.amount) ;
                    }, 0);

                    let key = acc.table_key
                    console.log("key", key)
                    arr[key] = parseFloat(sumAcc)
                    sumTotal = parseFloat(sumTotal) + parseFloat(sumAcc)
                })
                arr['sum_total'] = sumTotal
                memRows.push(arr)
            })

            return memRows
        },
        pytRecListData(){
            let totalAcc = this.totalAccounts
            let allTotalAcc  = this.allTotalAccount
            return {
                accountList : totalAcc,
                allTotalAccount  : allTotalAcc
            }

        }
    },
    methods: {
        setPayment(recordList){
            this.allTotalAccount = []
            _forEach(recordList, rl => {
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
        /*showAllList(){
            this.pytRecListData = {
                accountList : this.totalAccounts,
                allTotalAccount : this.allTotalAccount
            }

            setTimeout(() => {
                this.showListModal = true
            }, 500);
        },*/
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

                    let amount = this.getAmount(arr.key)
                    arr.amount = amount

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

                    let amount = this.getAmount(arr.key)
                    arr.amount = amount

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

                    let amount = this.getAmount(arr.key)
                    arr.amount = amount

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

                        let amount = this.getAmount(arr.key)
                        arr.amount = amount

                        allAccounts.push(arr)
                    }
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
        getInAllAccount(key, asSavings = false){
            if(asSavings){
                let getInd = this.allTotalAccount.findIndex(rs => { return rs.key == key})
            }
            let getInd = this.allTotalAccount.findIndex(rs => { return rs.key == key})
            return getInd
        },
        addAccounts(){

            let vm = this
            vm.loadingPage = true

            let isBalance = true
            let checkBalanceText = ""
            _forEach(vm.accountSelected.list, rs =>{
                let acct = cloneDeep(rs)
                if(rs.amount && Number(rs.amount) > 0){
                    if(rs.type == "LOAN" && !rs.is_prepaid && Number(rs.amount) > Number(rs.balance)){
                        isBalance = false
                        checkBalanceText += rs.product_name + ", "
                    }
                    else{
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
                    
                }

                if(rs.add_as_savings && Number(rs.add_as_savings) > 0 && rs.savings_data){
                    let getInd = vm.getInAllAccount(rs.key, true)
                    if(getInd >= 0){
                        vm.allTotalAccount[getInd].amount = Number(rs.add_as_savings)
                    }
                    else{
                        //Change data
                        acct.key = "LOAN_"+rs.account_no+"_SAVINGS_" + rs.savings_data.account_no
                        acct.account_no = rs.savings_data.account_no
                        acct.product_id = rs.savings_data.saving_product_id
                        acct.particular_id = rs.savings_data.product ? rs.savings_data.product.particular_id : 1
                        acct.product_name = rs.savings_data.product ? rs.savings_data.product.product_name : "Regular Savings"
                        acct.type = "SAVINGS"
                        acct.amount = rs.add_as_savings
                        acct.remarks = "From " + rs.product_name + " Payment"
                        vm.allTotalAccount.push(acct)
                    }
                }
            })
            vm.loadingPage = false

            if(!isBalance){
                checkBalanceText = checkBalanceText.slice(0, -2);
                let text = "Some PAYMENT is greater than LOAN BALANCE: " + checkBalanceText
                new Noty({
                    theme: 'relax',
                    type: "error",
                    layout: 'topRight',
                    text: text,
                    timeout: 3000
                }).show();
                return
            }

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
            let amount = this.getAmount(val)
            this.otherModel.amount = amount
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
                    arr.fullname = this.paymentModel.name_id
                }

                let key = "OTHERS_" + mdl.particular_id
                let getInd = this.allTotalAccount.findIndex(rs => { return rs.key == key && rs.member_id == arr.member_id})
                if(getInd >= 0){
                    this.allTotalAccount[getInd].amount = Number(mdl.amount)
                }
                else{
                    arr.key = "OTHERS_" + mdl.particular_id
                    arr.account_no = null
                    arr.product_id = mdl.particular_id // Particular
                    arr.particular_id = mdl.particular_id
                    arr.product_name = getParticular.name
                    arr.type = "OTHERS"
                    arr.amount = Number(mdl.amount)
                    
                    this.allTotalAccount.push(arr)
                }
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
            this.loadingPage = true
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
                        //text = "OR Number " + paymentModel.or_num + " already exist."
                        text = "This payment is already posted."
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
        /*'entryList': function(val){
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
        }*/
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