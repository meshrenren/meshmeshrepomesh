<template>
	<div class="general-voucher">
        <el-row :gutter="20">
            <el-col :span="17">
                <el-form :model="voucherModel" :rules="rulesVoucher" ref="voucherForm" label-width="160px" class="demo-ruleForm" label-position = "top">
                    <el-row :gutter="20">
                        <el-col :span="5">
                            <el-form-item label="Number" prop="gv_num" ref="gv_num">
                                <el-input type="text" v-model="voucherModel.gv_num" :disabled = "disabledVoucher"></el-input>
                            </el-form-item>
                        </el-col>

                        <el-col :span="9">
                            <el-form-item label="Name" prop="name_id" ref="name_id">
                                <el-select
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
                
                <el-form :model="rulesParticulars" :rules="rulesParticulars" ref="particularsForm" label-width="160px" class="demo-ruleForm" label-position = "top">
                    <el-row :gutter="20">
                        <el-col :span="11">
                            <el-form-item label="description" prop="description_id" ref="description_id">
                                <el-select v-model="particularsModel.description" :disabled = "disabledParticular" filterable placeholder="Select">
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
                            <el-form-item label="Debit" prop="debit" ref="debit">
                                <el-input type="number" v-model="particularsModel.debit" :disabled = "disabledParticular"></el-input>
                            </el-form-item>
                        </el-col>

                        <el-col :span="5">
                            <el-form-item label="Credit" prop="credit" ref="credit">
                                <el-input type="number" v-model="particularsModel.credit" :disabled = "disabledParticular"></el-input>
                            </el-form-item>
                        </el-col>

                        <el-col :span="3">  
                            <el-form-item label=" ">              
                                <el-button type = "primary" @click = "addEntry" :disabled = "disabledParticular">Add Entry</el-button>
                            </el-form-item>
                        </el-col>

                    </el-row>
                </el-form>
                
                <el-row :gutter="20">
                    <el-col :span="3">
                        <el-button type = "success" @click = "saveVoucherEntries">Finish</el-button>
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
            <el-col :span="7">

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

export default {
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
            allNameList             : [],
            entryList               : []
      	}
  	},
    created(){
        this.rulesVoucher = {
            gv_num: [ { required: true, message: 'Number cannot be blank.', trigger: 'blur' }
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
            console.log("nameId", nameId)
            let selectName = this.allNameList.find(rt => {
                return rt.value == nameId
            })
            console.log("selectName", selectName)
            if(selectName){
                return selectName.label
            }
            return "" 
        },
        getDescription(descId){
            console.log("descId", descId)
            let selectDesc = this.dataParticularList.find(rt => {
                return rt.id == descId
            })
            console.log("selectDesc", selectDesc)
            if(selectDesc){
                return selectDesc.name
            }
            return "" 
        },
        saveVoucherMain(){
            console.log("Save Voucher")
            this.$refs.voucherForm.validate((valid) => {
                if (valid){
                    this.disabledVoucher = true
                    this.disabledParticular = false
                }
            })
        },
        addEntry(){
            console.log("Add entry")
            this.$refs.particularsForm.validate((valid) => {
                if (valid){
                    let arr = this.voucherModel
                    arr['description_id'] = this.particularsModel.description_id
                    arr['debit'] = ""
                    if(this.particularsModel.debit)
                        arr['debit'] = Number(this.particularsModel.debit).toFixed(2)

                    arr['credit'] = ""
                    if(this.particularsModel.credit)
                        arr['credit'] = Number(this.particularsModel.credit).toFixed(2)

                    this.entryList.push(arr)

                    this.$refs.particularsForm.resetFields()
                }
            })
        },
        remoteMethod(query){
            console.log("query", query)
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
        mergeAll(member, division, station){
            let list = []
            _forEach(member, rs =>{
                let arr = {
                    value : 'member-' + rs.id,
                    label   : rs.first_name + " " + rs.middle_name + " " + rs.last_name
                }

                list.push(arr)
            })

            _forEach(station, rs =>{
                let arr = {
                    value : 'station-' + rs.id,
                    label   : rs.name
                }

                list.push(arr)
            })

            _forEach(division, rs =>{
                let arr = {
                    value : 'division-' + rs.id,
                    label   : rs.name
                }

                list.push(arr)
            })

            this.allNameList = list
        },
        getName(){
            this.$API.Voucher.getVoucherName()
            .then(result => {
                var res = result.data
                console.log(res)
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
            console.log(index, row)
            this.entryList.splice(index, 1)
        },
        saveVoucherEntries(){
            let vm = this
            vm.$swal({
                title: 'Add Entries?',
                text: "Are you sure you want to add entries in general voucher.",
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
                    _forEach(vm.entryList, el=>{
                        el['name'] = getVoucherName(el.name_id)

                        el['description_id'] = el.description_id
                        el['description'] = getDescription(el.description_id)
                        generalVoucherList.push(el)

                    })
                    console.log("generalVoucherList", generalVoucherList)
                    vm.$API.Voucher.saveVoucherEntries(generalVoucherList)
                    .then(result => {
                        var res = result.data
                        console.log(res)
                        //this.mergeAll(res.member, res.division, res.station)
                        vm.resetAll()
                    })
                    .catch(err => {
                        console.log(err)
                    })
                    .then(_ => { 
                        main_preloader.style.display = 'none'
                    })
                    
                }
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
        }
    }
  }
</script>
<style lang="scss">
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
