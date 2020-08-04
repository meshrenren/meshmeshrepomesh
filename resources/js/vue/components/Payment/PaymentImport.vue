<template>
	<div class="payment-record" v-loading = "loadingPage">
        <el-row :gutter="20">
            <!-- <el-col :span="10">
                <label>Last Column</label>
                <el-input v-model = "lastColumn"></el-input>
            </el-col> --><!-- 

            <el-col :span="10">
                <label>Import Payroll</label>
                <vue-dropzone id="importUpload"
                    v-on:vdropzone-sending="sendingEvent"
                    :options="dropzoneOptions">
                </vue-dropzone>
            </el-col> -->
             <el-col :span="7">
                <label>Import Payroll</label>
                <el-upload
                    class="upload-demo"
                    :action="dropzoneOptions.url"
                    :headers = "dropzoneOptions.headers"
                    :multiple = "false"
                    :on-success="setImportData"
                    accept=".xls,.xlsx"
                >
                  <el-button size="small" type="default" icon="el-icon-upload">Upload excel file here</el-button>
                </el-upload>
            </el-col>

            <el-col :span="10">
               <br>
                <el-alert title="" type="warning" class = "mt-5" :closable="false">
                    NOTE: <br>
                    * Be sure that the excel header columns matched with the label from <span @click = "showModal()" class = "to-click"> here</span>.<br>
                    * First member must start on row 3.
                </el-alert>
            </el-col>
            <el-col :span="24">
                <div class="box box-info mt-20">
                    <div class="box-body">
                        <el-row :gutter="20">
                            <el-col :span="16">
                                <el-form :model="paymentModel" :rules="rulesPayment" ref="paymentForm" label-width="130px" class="demo-ruleForm" label-position = "left">
                                    <el-row :gutter="20">
                                        <el-col :span="10">
                                            <el-form-item label="Name" prop="name_id" ref="name_id">
                                                <el-input type="text" v-model="paymentModel.name"></el-input>
                                            </el-form-item>
                                        </el-col>
                                        <el-col :span="10">                
                                            <el-form-item label="Date" prop="date_transact" ref="date_transact">
                                                <el-date-picker v-model="paymentModel.date_transact" type="date" placeholder="Pick a date">                      
                                                </el-date-picker>
                                            </el-form-item>
                                        </el-col>
                                        <el-col :span="10">
                                            <el-form-item label="OR Number" prop="or_num">
                                                <el-input type="text" v-model="paymentModel.or_num" ref="or_num"></el-input>
                                            </el-form-item>
                                        </el-col>
                                        <el-col :span="10">
                                            <el-form-item label="Check Number" prop="check_number">
                                                <el-input type="text" v-model="paymentModel.check_number" ref="check_number"></el-input>
                                            </el-form-item>
                                        </el-col>
                                    </el-row>
                                </el-form>
                            </el-col>
                            <el-col :span="24" v-if = "tableDataList.length > 0" class = "mt-10">
                                <el-table
                                    :data="tableDataList.filter(data => !nameSearch || data.fullname.toLowerCase().includes(nameSearch.toLowerCase()))"
                                    border striped
                                    style="width: 100%"
                                    :summary-method="getSummaries">
                                    <el-table-column
                                        width = "250px"
                                        prop="member_name">   
                                        <template slot="header" slot-scope="scope">
                                            <el-input v-model="nameSearch" size="mini" placeholder="Search Member"/>
                                        </template>                          
                                    </el-table-column>
                                    <el-table-column v-for="item in columnHeaderList"
                                        :key = "item.column_prop"
                                        :prop="item.column_prop"
                                        :label="item.column_label">   
                                        <template slot-scope="scope">
                                            {{ scope.row[item.column_prop] }} 
                                            <span v-if = "scope.row[item.column_prop+'_haserror']" class="ml-5">
                                                 <el-tooltip effect="dark" 
                                                    placement="top-start"
                                                    :content="hasError(item, scope.row.memberToPay)">
                                                    <span class="error-info"><i class="fa fa-info-circle"></i></span>
                                                </el-tooltip>
                                            </span> 
                                           
                                        </template>              
                                    </el-table-column>
                                    <el-table-column
                                        prop="total"
                                        label="Total">  
                                        <template slot-scope="scope"> 
                                            {{ $nf.formatNumber(scope.row.totalPayment) }} 
                                        </template>                      
                                    </el-table-column>
                                </el-table>
                            </el-col>
                            <el-col :span="24" class = "mt-10">
                                <el-button class = "auto-width pull-right " size = "small" type = "primary" @click = "savePayment()">SAVE</el-button>
                            </el-col>
                        </el-row>
                    </div>
                </div>
            </el-col>
        </el-row>
        <dialog-modal 
            title-header = "Click label below to copy."
            width = "95%"
            v-if="showListModal"
            :visible.sync="showListModal"
            @close="showListModal = false">
                <p>Please copy the label below that match to the payment in excel file.</p>
                <div class = "box box-primary particular-list">
                    <el-input size="small" v-model="searchKey" class = "search-key" placeholder = "Search label"></el-input> 
                    <el-row :gutter="20">
                        <el-col :span="6" v-for = "item in particularsList" :key = "item.id">
                            <div class = "list">
                                <el-input v-model="item.name" :disabled="true"></el-input> 
                                <input type="text" :value="item.name" :id = "'plist'+item.id" class = "hide-input" />
                                <el-button size="mini" type="default" icon="el-icon-copy-document" @click = "plistClicked('plist'+item.id)">Copy</el-button>
                            </div>
                        </el-col>
                    </el-row>
                </div>
        </dialog-modal> 
	</div>
</template>
<script>
    import 'vue2-dropzone/dist/vue2Dropzone.css'
    import vue2Dropzone from 'vue2-dropzone'

    window.noty = require('noty')
    import Noty from 'noty'  

    import cloneDeep from 'lodash/cloneDeep'    
    import _forEach from 'lodash/forEach'
    import _reduce from 'lodash/reduce'

export default {
    props:{
        pageData : {
            type : Object,
            require : true
        },
    },
    components: {
        vueDropzone: vue2Dropzone
    },
    data(){
        let formPayment  = cloneDeep(this.pageData.dataModel)
        return{
            loadingPage : false,
            dropzoneOptions: {
                url: this.$baseUrl+'/payment/set-imported-payment',
                addRemoveLinks: true,
                thumbnailWidth: 120,
                thumbnailHeight: 120,
                maxFilesize: 2.5,
                maxFiles: 1,
                acceptedFiles: '.xls,.xlsx',
                previewsContainer:'#perviewContainer',
                dictDefaultMessage: '<img src="'+this.$baseUrl+'/images/attachment_icon.png"><p class="drag-drop">' + 'Upload excel file here' + '</p>',
                headers: { 
                    "X-CSRF-TOKEN" : window.Yii.csrfToken
                },
            },
            lastColumn : null,
            searchKey  : '',
            showListModal : false,
            memberDataList      : [],
            columnHeaderList    : [],
            tableDataList       : [],
            nameSearch          : '',
            paymentModel        : formPayment,
            disableForm         : false,
            rulesPayment        : {},
            allAccounts         : []
        }
    },
    created(){
        this.rulesPayment = {
            or_num: [ { required: true, message: 'OR Number cannot be blank.', trigger: 'blur' }
            ],
            name: [ { required: true, message: 'Name cannot be blank.', trigger: 'blur' }
            ],
            date_transact: [ { required: true, message: 'Date cannot be blank.', trigger: 'blur' }
            ],           
        } 
    },
    methods:{
        showModal(){
            console.log("showModal")
            this.showListModal = true
        },
        sendingEvent (file, xhr, formData) {
            formData.append('lastColumn', lastColumn);
        },
        plistClicked(val){
            let text = document.querySelector('#'+val)
            text.setAttribute('type', 'text')
            text.select()
            document.execCommand("copy")

        },
        setImportData(res, file){
            console.log("setImportData", res, file)

            this.memberDataList = res.dataList
            this.columnHeaderList = res.particularItem
            this.setTableData()
        },
        setTableData(){
            let memList = cloneDeep(this.memberDataList)
            let list = []
            let allAcc = []
            _forEach(memList, mem =>{
                if(mem.memberData){
                    let arr = {}
                    arr.member_name = mem.memberData.last_name + ", " + mem.memberData.first_name
                    arr.member_id = mem.memberData.id
                    let total = 0

                    _forEach(mem.memberToPay, pay => {
                        let val = null

                        if(pay.error_status){
                            arr['particular_'+pay.particular_id+"_haserror"] = true
                        }
                        else{
                            if(pay.amount > 0){
                                val = pay.amount
                                total += val

                                let arrAcc = {}
                                arrAcc.member_id = mem.memberData.id
                                arrAcc.amount = pay.amount
                                arrAcc.type = pay.category
                                arrAcc.particular_id = pay.particular_id
                                arrAcc.product_id = pay.product_id
                                arrAcc.account_no = pay.account_id
                                arrAcc.is_prepaid = pay.is_prepaid

                                allAcc.push(arrAcc)
                            }
                            if(pay.arrears && pay.arrears > 0){
                                arr['particular_'+pay.particular_id+"_haserror"] = true
                            }
                        }
                        

                        arr['particular_'+pay.particular_id] = val


                    })

                    arr['memberToPay'] = mem.memberToPay
                    arr.totalPayment = total
                    list.push(arr)
                }
            })

            this.tableDataList = list
            this.allAccounts = allAcc
        },
        getSummaries(param){
            const { columns, data } = param;
            const sums = [];

            columns.forEach((column, index) => {
                if (index === 0) {
                    sums[index] = 'Total';
                    return;
                }

                const values = data.map(item => Number(item[column.property]));
                if (!values.every(value => isNaN(value))) {
                    let sumAmount = values.reduce((prev, curr) => {
                        const value = Number(curr);
                        if (!isNaN(value)) {
                            return prev + curr;
                        } else {
                            return prev;
                        }
                    }, 0);
                    sums[index] = ' ' + this.$nf.formatNumber(sumAmount)
                } else {
                    sums[index] = 'N/A';
                }
            });

            return sums;
        },
        hasError(particular, payments){
            let getPayment = payments.find(fe => Number(particular.id) == Number(fe.particular_id))

            console.log('hasError', particular.id, getPayment)
            if(getPayment){
                let msg = ""
                if(getPayment.error_status == "balance_negative"){
                    msg = "Principal Balance will be negative. Current balance: " + getPayment.accountDetails.principal_balance + ". " + "Please proceed to Close Loan facility if you wish to close the account."
                }
                else if(getPayment.error_status == "balance_zero"){
                    msg = "Principal Balance will be zero. Current balance: " + getPayment.accountDetails.principal_balance + ". " + "Please proceed to Close Loan facility if you wish to close the account."
                }
                else if(getPayment.error_status == "no_loan_account"){
                    msg = "No Account."
                }
                else{
                    msg = "Has an error validating the account. Please contact administrator."
                    if(getPayment.arrears && getPayment.arrears > 0){
                        msg = "Has Arrear: " + this.$nf.formatNumber(getPayment.arrears, 2)
                    }
                }
                return msg
            }
            
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
        savePayment(){
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

                    vm.savePaymentList(model, vm.allAccounts)
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
                    
                    location.reload()
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
                this.loadingPage = false
            })
        },
    },
    computed:{
        particularsList(){
            let list = cloneDeep(this.pageData.dataParticulars)
            if(this.searchKey){
                list = list.filter(ls => {
                    return ls.name.toLowerCase().indexOf(this.searchKey) > -1
                })
            }
            return list
        }
    }
}
</script>
<style lang="scss">
    #perviewContainer{
        .dz-image, .dz-progress, 
        .dz-success-mark, .dz-error-mark,
        .dz-size {
            display: none;
        }

        .dz-details, .dz-error-message{
            display: inline-block;
        }

        .dz-details span{
            font-weight: 600;
            text-decoration: underline;
        }

        .dz-remove{
            display: inline;
        }
    }

    #importUpload{
        min-height: unset;
        padding: 5px;

        .dz-message {
            margin: 1px 0px;

            img {
                width: 20px;
            }

            .drag-drop {
                display: inline;
                margin: unset;
                margin-left: 10px;
            }
        }
    }
    .payment-record{
        .particular-list{
            .search-key{
                padding: 5px 14px;
            }
            .list{
                padding: 2px 14px;
                position: relative;

                .el-input{
                    z-index: 1000;
                    input{
                        color: #000 !important;
                    }  
                }

                .hide-input{
                    position: absolute;
                    left: 20px;
                    height: 14px;
                    top: 9px;
                    width: 50px;
                    z-index: 0;
                }
            }

            .el-input{
                width: unset;
            }
        }

        .to-click{
            font-weight: bold;
            cursor: pointer;
        }

        .el-form-item{
            margin-bottom: 0px !important;

            .el-form-item__error{
                position: initial;
            }
        }
    }
    .el-dialog__body{
        padding: 0px 20px;
    }
</style>