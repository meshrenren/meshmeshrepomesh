<template>
	<div class="general-voucher">
        <el-row :gutter="40">
            <el-col :span="16">
                <el-form :model="voucherModel" :rules="rulesVoucher" ref="voucherForm" label-width="160px" class="demo-ruleForm" label-position = "top">
                    <el-row :gutter="20">
                        <el-col :span="4">
                            <el-form-item label="Number" prop="gv_num" ref="gv_num">
                                <el-input type="text" v-model="voucherModel.gv_num" :disabled = "disabledVoucher"></el-input>
                            </el-form-item>
                        </el-col>

                        <el-col :span="7">
                            <el-form-item label="Name" prop="name_id" ref="name_id">
                                <el-select
                                    @change = "changeName"
                                    v-model="voucherModel.name_id"
                                    filterable
                                    remote allow-create
                                    reserve-keyword
                                    :disabled = "disabledVoucher"
                                    placeholder="Please enter name"
                                    :remote-method="remoteMethod"
                                    :loading="loading">
                                    <el-option
                                      v-for="item in nameList"
                                      :key="item.value"
                                      :label="item.label"
                                      :value="item.value">
                                    </el-option>
                                </el-select>
                            </el-form-item>
                        </el-col>
                        <el-col :span = "3">
                            <el-form-item label="" prop="type" ref="type">
                                <span><el-radio v-model="voucherModel.type" label="Individual">Individual</el-radio></span>
                                <span><el-radio v-model="voucherModel.type" label="Group">Group</el-radio></span>
                            </el-form-item>
                        </el-col>

                        <el-col :span="7">                
                            <el-form-item label="Date" prop="date_transact" ref="date_transact">
                                <el-date-picker v-model="voucherModel.date_transact" :disabled = "disabledVoucher" type="date" placeholder="Pick a date">                                    
                                </el-date-picker>
                            </el-form-item>
                        </el-col>

                        <el-col :span="3"> 
                            <el-form-item label=" ">               
                                <el-button type = "primary" @click = "saveVoucherMain" :disabled = "disabledVoucher">Save</el-button>
                            </el-form-item>
                        </el-col>
                    </el-row>        
                </el-form>
                
                <el-form :model="particularsModel" :rules="rulesParticulars" ref="particularsForm" label-width="160px" class="demo-ruleForm" label-position = "top">
                    <el-row :gutter="20">
                        <el-col :span="11">
                            <el-form-item label="Description" prop="description_id">
                                <el-select v-model="particularsModel.description_id" :disabled = "disabledParticular" filterable placeholder="Select"  ref="description_id">
                                    <el-option
                                        v-for="item in dataParticularList"
                                        :key="parseInt(item.id)"
                                        :label="item.name"
                                        :value="parseInt(item.id)">
                                    </el-option>
                                </el-select>
                            </el-form-item>
                        </el-col>

                        <el-col :span="5">
                            <el-form-item label="Debit" prop="debit">
                                <el-input type="number" v-model="particularsModel.debit" :disabled = "disabledParticular" @keyup.enter.native = "addEntry" ref="debit"></el-input>
                            </el-form-item>
                        </el-col>

                        <el-col :span="5">
                            <el-form-item label="Credit" prop="credit">
                                <el-input type="number" v-model="particularsModel.credit" :disabled = "disabledParticular" @keyup.enter.native = "addEntry" ref="credit"></el-input>
                            </el-form-item>
                        </el-col>

                        <el-col :span="3">  
                            <el-form-item label=" ">              
                                <el-button type = "primary" @click = "addEntry" :disabled = "disabledParticular">Add Entry</el-button>
                            </el-form-item>
                        </el-col>

                        <el-col :span="11">
                            <p></p>
                        </el-col>

                        <el-col :span="5">
                            <el-form-item label="Total Debit">
                                <el-input type="number" v-model="totalDebit" disabled></el-input>
                            </el-form-item>
                        </el-col>

                        <el-col :span="5">
                            <el-form-item label="Total Credit">
                                <el-input type="number" v-model="totalCredit" disabled></el-input>
                            </el-form-item>
                        </el-col>

                        <el-col :span="3">  
                            <p></p>
                        </el-col>

                    </el-row>
                </el-form>

                <el-row :gutter="20">
                    <el-col :span="3">
                        <el-button type = "success" @click = "createVoucher(false)">Finish</el-button>
                    </el-col>
                    <el-col :span="3">
                        <el-button type = "danger" @click = "cancelVoucher">Cancel</el-button>
                    </el-col>
                </el-row>
                
                <div class = "voucher-sample-form">
                    <h3>VOUCHER</h3>
                    <el-table
                        :data="entryList"
                        border striped
                        style="width: 100%"
                        height = "400px">
                        <el-table-column
                            prop="date_transact"
                            label="Date">
                            <template slot-scope="scope">
                                {{ $df.formatDate(scope.row.date_transact, "YYYY-MM-DD")}}
                            </template>
                            
                        </el-table-column>
                        <el-table-column
                            prop="gv_num"
                            label="GV Number">
                        </el-table-column>
                        <el-table-column
                            prop="description_id"
                            label="Description">
                            <template slot-scope="scope">
                                {{ getDescription(scope.row.description_id)}}
                            </template>
                        </el-table-column>
                        <el-table-column
                            prop="debit"
                            label="Debit">
                        </el-table-column>
                        <el-table-column
                            prop="credit"
                            label="Credit">
                        </el-table-column>
                        <el-table-column
                            prop="name_id"
                            label="Name">
                            <template slot-scope="scope">
                                {{ getVoucherName(scope.row.name_id)}}
                            </template>
                        </el-table-column>
                        <el-table-column
                            label="Action">
                            <template slot-scope="scope">
                                <el-button
                                  size="mini"
                                  type="warning"
                                  @click="handleRemove(scope.$index, scope.row)">Remove</el-button>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
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


    import {getNameList} from '../../mixins/getNameList.js'

export default {
    mixins: [getNameList],
    props: ['dataModel', 'dataParticularList'],
    data: function () {    	
    	let voucher  = {}
  		this.dataModel.forEach(function(detail){
  			voucher[detail] = null
  		})
        let formVoucher = {gv_num : null, name_id : null, name : null, date_transact: null, type: null, type_id : null}

        let formParticulars = {description_id : null, debit : null, credit : null}

      	return {
      		voucherModel			: formVoucher,
            particularsModel        : formParticulars,
            rulesVoucher            : {},
            rulesParticulars        : {},
            disabledVoucher         : false,
            disabledParticular      : true,
            nameList                : [],
            loading                 : false,
            entryList               : [],
            totalDebit              : 0,
            totalCredit             : 0,
            memberAccounts          : {savings : [], share: [], time_deposit : []},
            loadingTable            : false
      	}
  	},
    created(){
        this.rulesVoucher = {
            gv_num: [ { required: true, message: 'GV Number cannot be blank.', trigger: 'blur' }
            ],
            name_id: [ { required: true, message: 'Name cannot be blank.', trigger: 'blur' }
            ],
            date_transact: [ { required: true, message: 'Date cannot be blank.', trigger: 'blur' }
            ],            
        } 

        this.rulesParticulars = {
            description_id: [ { required: true, message: 'Description cannot be blank.', trigger: 'blur' }
            ],           
        } 

        this.getName()
    },
    methods:{
        getVoucherName(nameId){
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
        saveVoucherMain(){
            this.$refs.voucherForm.validate((valid) => {
                if (valid){
                    this.disabledVoucher = true
                    this.disabledParticular = false

                    this.$refs.description_id.focus()
                }
            })
        },
        resetAccount(){
            this.memberAccounts.loans = []
            this.memberAccounts.savings = []
            this.memberAccounts.share = []
            this.memberAccounts.time_deposit = []
        },
        changeName(val){
            //get type and type id
            let nameSplit = val
            nameSplit = nameSplit.split("-")
            this.voucherModel.type = "Individual"
            this.voucherModel.type_id = null
            let id = null
            if(nameSplit.length > 1){
                if(nameSplit[0] == 'station' || nameSplit[0] == 'division'){
                    this.voucherModel.type = "Group"
                }
                if(nameSplit[0] == 'member'){
                    id = nameSplit[1]
                }

                if(nameSplit[0] == 'station' || nameSplit[0] == 'division' || nameSplit[0] == 'member'){
                    this.voucherModel.type_id = nameSplit[1]
                }
            }

            let name = this.getVoucherName(val)
            this.resetAccount()
            this.loadingTable = true

            this.$API.Member.getAccounts(this.voucherModel.type, id, name)
            .then(result => {
                var res = result.data
                console.log(res)
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
        addEntry(){
            this.$refs.particularsForm.validate((valid) => {
                if (valid){
                    let arr = cloneDeep(this.voucherModel)
                    let particulars = cloneDeep(this.particularsModel)

                    arr['description_id'] = particulars.description_id
                    arr['debit'] = ""
                    if(particulars.debit){
                        arr['debit'] = Number(particulars.debit).toFixed(2)
                    }

                    arr['credit'] = ""
                    if(particulars.credit){
                        arr['credit'] = Number(particulars.credit).toFixed(2)
                    }

                    this.entryList.push(arr)

                    this.$refs.particularsForm.resetFields()

                    this.$refs.description_id.focus()
                }
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
        getName(){
            this.$API.Voucher.getVoucherName()
            .then(result => {
                var res = result.data
                this.mergeAll(res.member, res.division, res.station)
            })
            .catch(err => {
                console.log(err)
            })
            .then(_ => { 
                main_preloader.style.display = 'none'
            })
        
        },
        handleRemove(index, row){
            this.entryList.splice(index, 1)
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
        createVoucher(isForceAdd){
            let vm = this

            if(this.validateEntries()){
                return
            }
            let title = 'Add Entries?'
            let text = "Are you sure you want to add entries in general voucher?"
            if(isForceAdd){
                text = "GV Number already exist. Are you sure you want to add entries in general voucher?"
            }
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
                    let list = cloneDeep(vm.entryList)
                    _forEach(list, el=>{
                        el['name'] = vm.getVoucherName(el.name_id)
                        el['description_id'] = el.description_id
                        el['description'] = vm.getDescription(el.description_id)
                        el['date_transact'] = vm.$df.formatDate(el.date_transact, "YYYY-MM-DD")
                        generalVoucherList.push(el)

                    })
                    vm.saveVoucherEntries(generalVoucherList, vm.voucherModel.gv_num, isForceAdd)
                }
            })
        },
        saveVoucherEntries(generalVoucherList, gvNumber, isForceAdd){
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
        cancelVoucher(){
            let vm = this
            vm.$swal({
                title: 'Cancel Inputs?',
                text: "Are you sure you want to cancel all entries.",
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
            this.$refs.voucherForm.resetFields()
            this.disabledVoucher = false
            this.$refs.particularsForm.resetFields()
            this.disabledParticular = true
            this.entryList = []
            this.totalCredit = 0
            this.totalDebit = 0
        }
    },
    watch:{
        'entryList': function(val){
            let totalD = 0
            let totalC = 0
            if(val.length > 0){
                _forEach(val, vl =>{
                    if(vl.debit){
                        totalD = parseFloat(totalD) + parseFloat(vl.debit)
                    }

                    if(vl.credit){
                        totalC = parseFloat(totalC) + parseFloat(vl.credit)
                    }
                })
            }

            this.totalCredit = totalC
            this.totalDebit = totalD
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
        .el-form-item{
            .el-form-item__label{
                line-height: 10px;
                padding: 0 0 0px;
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

        .voucher-sample-form{
            margin-top: 20px;

            h3{
                text-align: center;
                font-weight: bold;
            }

            .el-table{
                margin-top:10px;
            }
        }

        
    }

</style>
