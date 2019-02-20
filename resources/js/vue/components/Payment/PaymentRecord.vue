<template>
	<div class="payment-record">
		<el-row :gutter="20">
            <el-col :span="17">
                <el-form :model="paymentModel" :rules="rulesPayment" ref="paymentForm" label-width="160px" class="demo-ruleForm" label-position = "top">
                    <el-row :gutter="20">
                        <el-col :span="10">
                            <el-form-item label="Name" prop="name_id" ref="name_id">
                                <el-select
                                    @change = "changeName"
                                    v-model="paymentModel.name_id"
                                    filterable
                                    remote allow-create
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

                        <el-col :span="12">
                            <el-form-item label="Description" prop="particular_id">
                                <el-select v-model="paymentModel.particular_id" filterable placeholder="Select"  ref="particular_id">
                                    <el-option
                                        v-for="item in dataParticularList"
                                        :key="parseInt(item.id)"
                                        :label="item.name"
                                        :value="parseInt(item.id)">
                                    </el-option>
                                </el-select>
                            </el-form-item>
                        </el-col>

                        <el-col :span="6">
                            <el-form-item label="Amount" prop="amount_paid">
                                <el-input type="number" :min = "0" v-model="paymentModel.amount_paid" @keyup.enter.native = "addEntry" ref="debit"></el-input>
                            </el-form-item>
                        </el-col>

                        <el-col :span="6">
                            <el-form-item label="Total" >
                                <el-input type="number" v-model="totalAmount" :disabled = "true"></el-input>
                            </el-form-item>
                        </el-col>
                    </el-row>
                </el-form>
                <el-row :gutter="20">
                    <el-col :span="3">
                        <el-button type = "primary" @click = "addEntry()">Add Entry</el-button>
                    </el-col>
                    <el-col :span="3">
                        <el-button type = "success" @click = "finishPayment(false)">Finish</el-button>
                    </el-col>
                    <el-col :span="3">
                        <el-button type = "danger" @click = "cancelVoucher">Cancel</el-button>
                    </el-col>
                </el-row>

                <div class = "psyment-entry-list">
                    <h3>Description List</h3>
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
                            prop="particular_id"
                            label="Description">
                            <template slot-scope="scope">
                                {{ getDescription(scope.row.particular_id)}}
                            </template>
                        </el-table-column>
                        <el-table-column
                            prop="amount_paid"
                            label="Amount">
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
        let formPayment  = {}
        this.dataModel.forEach(function(detail){
            formPayment[detail] = null
        })
        formPayment['name_id'] = null
    	return {
            paymentModel        : formPayment,
            disableForm         : false,
            rulesPayment        : {},
            nameList		    : [],
            totalAmount         : 0,
            stationName         : '',
            loading             : false,
            entryList           : []
    	}
    },
    created(){

        console.log("this.$API.Payroll", this.$API.Payroll)

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
    },
    methods: {
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
                this.mergeAll(res.member, res.division, res.station)
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
        changeName(val){
            //get type and type id
            let nameSplit = val
            nameSplit = nameSplit.split("-")
            this.paymentModel.type = "Individual"
            this.paymentModel.type_id = null
            if(nameSplit.length > 1){
                if(nameSplit[0] == 'station' || nameSplit[0] == 'division'){
                    this.paymentModel.type = "Group"
                }
                if(nameSplit[0] == 'station' || nameSplit[0] == 'division' || nameSplit[0] == 'member'){
                    this.paymentModel.type_id = nameSplit[1]
                }
            }

            this.stationName = ""
            let getVal = this.allNameList.find(rs => {return val == rs.value})
            if(getVal){
                this.stationName = getVal.station_name
            }
        },
        addEntry(){
            this.$refs.paymentForm.validate((valid) => {
                if (valid){
                    let form = cloneDeep(this.paymentModel)
                    let arr = form

                    arr['name'] = this.getPaymentName(form.name_id)
                    arr['particular_id'] = form.particular_id
                    arr['amount_paid'] = Number(form.amount_paid).toFixed(2)

                    this.entryList.push(arr)

                    this.paymentModel.particular_id = null
                    this.paymentModel.amount_paid = 0

                    this.$refs.particular_id.focus()
                }
            })

            if(this.entryList.length == 1){
                this.disableForm = true
            }
        },
        handleRemove(index, row){
            this.entryList.splice(index, 1)
        },
        validateList(){
            let text = ""
            let type = "error"
            let hasError = false
            if(this.entryList.length <= 0){
                hasError = true
                text = "Please add list."
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
        finishPayment(isForceAdd){
            let vm = this

            if(this.validateList()){
                return;
            }
            let title = 'Add Payment?'
            let text = "Are you sure you want to save payment list?"
            if(isForceAdd){
                text = "OR Number already exist. Are you sure you want to save payment list?"
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
                    let list = cloneDeep(vm.entryList)
                    _forEach(list, el=>{
                        el['date_transact'] = vm.$df.formatDate(el.date_transact, "YYYY-MM-DD")

                    })
                    vm.savePaymentList(list, vm.paymentModel.or_num, isForceAdd)
                }
            })
        },
        savePaymentList(list, orNum, isForceAdd){
            this.$API.Payment.savePaymentList(list, orNum, isForceAdd)
            .then(result => {
                var res = result.data
                if(res.hasError){
                    if(res.error == 'has_ornum'){
                        this.createVoucher(true)
                    }
                    else{
                        new Noty({
                            theme: 'relax',
                            type: 'warning',
                            layout: 'topRight',
                            text: 'Some payment list not successfully saved.',
                            timeout: 3000
                        }).show();
                        this.resetAll()
                    }
                }
                else{
                    new Noty({
                        theme: 'relax',
                        type: 'success',
                        layout: 'topRight',
                        text: 'Payment list successfully saved.',
                        timeout: 3000
                    }).show();
                    this.resetAll()
                }
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
            this.$refs.paymentForm.resetFields()
            this.entryList = []
            this.totalAmount = 0
            this.stationName = 0
            this.disableForm = false
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

        hr{
            border-top: 1px solid #d6d6d6;
            margin-top: 10px;
            margin-bottom: 10px;
        }
    }

	
</style>