<template>
	<div class="voucher-form">
        <el-form :model="voucherModel" :rules="rulesVoucher" ref="voucherForm" label-width="160px" class="demo-ruleForm" label-position = "top">
            <el-row :gutter="20" v-if = "dataDefault == null">
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
                            filterable default-first-option
                            remote :allow-create = "allowCreateName"
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
                    <el-form-item label="Description" prop="particular_id">
                        <el-select v-model="particularsModel.particular_id" :disabled = "disabledParticular" filterable placeholder="Select"  ref="particular_id">
                            <el-option
                                v-for="item in particularList"
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
                        <el-input type="number" v-model="totalAmount.totalDebit" disabled></el-input>
                    </el-form-item>
                </el-col>

                <el-col :span="5">
                    <el-form-item label="Total Credit">
                        <el-input type="number" v-model="totalAmount.totalCredit" disabled></el-input>
                    </el-form-item>
                </el-col>

                <el-col :span="3">  
                    <p></p>
                </el-col>

            </el-row>
        </el-form>
        
        <div class="box box-primary voucher-sample-form">
            <div class="box-header">
                <h3 class="box-title">VOUCHER</h3>
                <div class="box-tools pull-right">
                    <el-button class = "auto-width ml-5" type = "success" size = "large" @click = "createVoucher()" :disabled = "btnDisable">Finish</el-button>
                    <el-button class = "auto-width ml-5" type = "danger"size = "large"  @click = "cancelVoucher" :disabled = "btnDisable">Cancel</el-button>
                </div>
            </div>
            <div class="box-body">
                <el-table
                    class = "mt-10"
                    :data="entryList"
                    border striped
                    style="width: 100%"
                    height = "350px">
                    <el-table-column
                        prop="date_transact"
                        label="Date">
                        <template slot-scope="scope">
                            {{ $df.formatDate(voucherModel.date_transact, "YYYY-MM-DD")}}
                        </template>
                        
                    </el-table-column>
                    <el-table-column
                        prop="particular_id"
                        label="Description">
                        <template slot-scope="scope">
                            <el-select v-model="scope.row.particular_id" filterable placeholder="Select">
                                <el-option
                                    v-for="item in particularList"
                                    :key="parseInt(item.id)"
                                    :label="item.name"
                                    :value="parseInt(item.id)">
                                </el-option>
                            </el-select>
                        </template>
                    </el-table-column>
                    <el-table-column
                        prop="debit"
                        label="Debit">
                        <template slot-scope="scope">
                            <el-input type="number" v-model="scope.row.debit" ></el-input>
                        </template>
                    </el-table-column>
                    <el-table-column
                        prop="credit"
                        label="Credit">
                        <template slot-scope="scope">
                            <el-input type="number" v-model="scope.row.credit" ></el-input>
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
        </div>
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
    props: {
        allowCreateName : {
            type: Boolean,
            default : true
        },
        dataModel: {
            type: Object,
            default: function () { return {} },
            required: true
        },
        dataDetailsModel: {
            type: Object,
            default: function () { return {} },
            required: true
        },
        dataParticularList: {
            type: Array,
            default: function () { return [] },
            required: true
        },
        dataEntryList: {
            type: Array,
            default: function () { return [] },
            required: false
        },
        showButtons: {
            type: Boolean,
            default : true,
            required: false
        },
        dataDefault: {
            type: Object,
            default : null,
            required: false
        }
    },
    data: function () {    	
    	let formVoucher  = cloneDeep(this.dataModel)
        formVoucher['name_id'] = null
        formVoucher['member_id'] = null

        let formParticulars = this.dataDetailsModel

      	return {
      		voucherModel			: formVoucher,
            particularsModel        : formParticulars,
            particularList          : this.dataParticularList,
            rulesVoucher            : {},
            rulesParticulars        : {},
            disabledVoucher         : false,
            disabledParticular      : true,
            nameList                : [],
            loading                 : false,
            entryList               : this.dataEntryList,
            loadingTable            : false,
            btnDisable              : true
      	}
  	},
    created(){
        if(this.dataDefault){
            _forEach(this.dataDefault, (key, value) =>{
                console.log("dataDefault", key, value)
            })
        }
        this.rulesVoucher = {
            gv_num: [ { required: true, message: 'GV Number cannot be blank.', trigger: 'blur' }
            ],
            name_id: [ { required: true, message: 'Name cannot be blank.', trigger: 'blur' }
            ],
            date_transact: [ { required: true, message: 'Date cannot be blank.', trigger: 'blur' }
            ],            
        } 

        this.rulesParticulars = {
            particular_id: [ { required: true, message: 'Description cannot be blank.', trigger: 'blur' }
            ],           
        } 

        this.getName()

        this.$EventDispatcher.listen('RESET_DATA', data => {
            this.resetAll()
        })
    },
    computed:{
        totalAmount(){
            let entry = this.entryList
            let totalD = 0
            let totalC = 0
            this.btnDisable = true
            if(entry.length > 0){
                _forEach(entry, vl =>{
                    if(vl.debit){
                        totalD = parseFloat(totalD) + parseFloat(vl.debit)
                    }

                    if(vl.credit){
                        totalC = parseFloat(totalC) + parseFloat(vl.credit)
                    }
                })

                this.btnDisable = false
            }

            return {
                totalCredit : totalC,
                totalDebit : totalD
            }
        }
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
            console.log("voucherModel", this.voucherModel);
            this.$refs.voucherForm.validate((valid) => {
                if (valid){
                    this.disabledVoucher = true
                    this.disabledParticular = false

                    this.$refs.particular_id.focus()
                }
            })
        },
        changeName(val){
            //get type and type id
            let nameSplit = val
            nameSplit = nameSplit.split("-")
            this.voucherModel.type = "Individual"
            this.voucherModel.member_id = null
            let id = null
            if(nameSplit.length > 1){
                if(nameSplit[0] == 'station' || nameSplit[0] == 'division'){
                    this.voucherModel.type = "Group"
                }
                if(nameSplit[0] == 'member'){
                    id = nameSplit[1]
                    this.voucherModel.member_id = id
                }

                if(nameSplit[0] == 'station' || nameSplit[0] == 'division' || nameSplit[0] == 'member'){
                    this.voucherModel.type_id = nameSplit[1]
                }
            }

            let name = this.getVoucherName(val)
            this.voucherModel.name = name

            let data = {type : null, name : name, id : id}
            this.$EventDispatcher.fire('CHANGE_NAME', data)
        },
        addEntry(){
            this.$refs.particularsForm.validate((valid) => {
                if (valid){

                    let vModel = cloneDeep(this.voucherModel)
                    let arr = cloneDeep(this.particularsModel)

                    this.$set(arr, 'member_id', vModel.member_id)  
                    this.$set(arr, 'debit', 0)
                    this.$set(arr, 'credit', 0)

                    if(this.particularsModel.debit){
                        arr.debit = Number(this.particularsModel.debit).toFixed(2)
                    }

                    if(this.particularsModel.credit){
                        arr.credit = Number(this.particularsModel.credit).toFixed(2)
                    }

                    this.entryList.push(arr)

                    this.$refs.particularsForm.resetFields()

                    this.$refs.particular_id.focus()
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
            if(this.totalAmount.totalDebit != this.totalAmount.totalCredit){
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
        createVoucher(){
            let vm = this

            if(this.validateEntries()){
                return
            }

            let list = cloneDeep(vm.entryList)
            let vModel = cloneDeep(vm.voucherModel)
            vModel.date_transact = vm.$df.formatDate(vModel.date_transact, "YYYY-MM-DD")
            let data = {voucherModel : vModel, entryList : list}

            this.$emit('finishvoucher', {data: data})
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
            this.totalAmount.totalCredit = 0
            this.totalAmount.totalDebit = 0
        }
    },
    watch:{
    }
  }
</script>
<style lang="scss">
    
    @import '~noty/src/noty.scss';
    .voucher-form{
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
                font-weight: bold;
                font-size: 24px;
                margin-top: 8px;
            }
        }

        
    }

</style>
