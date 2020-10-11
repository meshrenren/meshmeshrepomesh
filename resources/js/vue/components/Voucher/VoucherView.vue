<template>
	<div class="view-general-voucher">
        <el-row>
            <el-col :span="20">
                <el-row :gutter="20">
                    <el-col :span="6">
                        <el-input type="text" v-model="gv_num" placeholder = "Enter GV Number" @keyup.enter.native = "locateVoucherNumber"></el-input>
                    </el-col>
                    <el-col :span="3">
                        <el-button type = "primary" @click = "locateVoucherNumber">Locate</el-button>
                    </el-col>
                    <el-col :span="3">
                        <el-button type = "info" @click = "showModal = true">View All GV</el-button>
                    </el-col>
                    <el-col :span="3">
                        <el-button type = "default" @click = "printVoucher">Print</el-button>
                    </el-col>
                    <!-- <el-col :span="3">
                        <el-button type = "success">Add</el-button>
                    </el-col> -->
                    <el-col :span="24">
                        
                        <div class = "voucher-sample-form" id = "voucherSampleForm" v-loading = "loadingTable">
                            <h3>GENERAL VOUCHER</h3>
                            <table class = "voucher-detail pcr-details">
                                <tr>
                                    <th class = "tlabel" style="text-align: left;">Name: </th>
                                    <td class = "tvalue"> {{ voucher.name }} </td>
                                    <td style="width:50px"></td>
                                    <th class = "tlabel" style="text-align: left;">Date Transact: </th>
                                    <td class = "tvalue">{{ $df.formatDate(voucher.date_transact, "YYYY-MM-DD")}}</td>
                                </tr>
                                <tr>
                                    <th class = "tlabel" style="text-align: left;">GV Number: </th>
                                    <td class = "tvalue">{{ voucher.gv_num }}</td>
                                    <td style="width:50px"></td>
                                    <template v-if = "voucher.id && !voucher.posted_date">
                                        <td colspan="2" > 
                                            <el-button class = "auto-width mt-10 mr-10 " size = "small" type = "primary" @click = "updateVoucher()" v-if = "voucher.id && !voucher.posted_date">UPDATE</el-button>
                                            <el-button class = "auto-width mt-10" size = "small" type = "success" @click = "finishVoucher()" v-if = "voucher.id && !voucher.posted_date">POST</el-button>
                                        </td>
                                    </template>
                                    <template v-else-if = "voucher.id && voucher.posted_date">
                                        <th class = "tlabel" style="text-align: left;">Posted Date: </th>
                                        <td class = "tvalue">{{ $df.formatDate(voucher.posted_date, "YYYY-MM-DD")}}</td>
                                    </template>
                                    
                                </tr>
                                <tr v-if = "voucher.cancelled_date">
                                    <th class = "tlabel" style="text-align: left;">Status: </th>
                                    <td class = "tvalue"> CANCELLED </td>
                                </tr>
                            </table>
                            <el-table
                                class = "mt-20 el-table-bordered"
                                :data="entryList"
                                border striped
                                style="width: 100%"
                                :summary-method="getSummaries"
                                show-summary
                                min-height = "400px"
                                max-height = "400px">
                                <el-table-column
                                    style="text-align: left;"
                                    prop="particular.name"
                                    label="Description">
                                </el-table-column>
                                <el-table-column
                                    prop="debit"
                                    label="Debit">
                                    <template slot-scope="scope" v-if = "parseFloat(scope.row.debit) > 0">
                                        {{ $nf.formatNumber(scope.row.debit, 2) }}
                                    </template>
                                </el-table-column>
                                <el-table-column
                                    prop="credit"
                                    label="Credit">
                                    <template slot-scope="scope" v-if = "parseFloat(scope.row.credit) > 0">
                                        {{ $nf.formatNumber(scope.row.credit, 2) }}
                                    </template>
                                </el-table-column>
                            </el-table>
                        </div>
                    </el-col>
                </el-row>
            </el-col>
        </el-row>
        <all-vouchers :show-modal = "showModal" @select="selectVoucher" @close = "showModal = false" >
        </all-vouchers>
	</div>
</template>

<script>
	window.noty = require('noty')
    import Noty from 'noty'  
    import cloneDeep from 'lodash/cloneDeep'    
    import _forEach from 'lodash/forEach'

    import AllVouchers from '../General/AllVouchers.vue'  
    import VueHtmlToPaper from 'vue-html-to-paper';

    const printOptions = {
        name: '_blank',
        specs: [
            'fullscreen=yes',
            'titlebar=yes',
            'scrollbars=yes'
        ],
        styles: [
            window.Yii.baseUrl + "/css/bootstrap.min.css",
            window.Yii.baseUrl + "/css/mpdf.css"
        ]
    }
    Vue.use(VueHtmlToPaper, printOptions);  

export default {
    components: { AllVouchers },
    props: ['dataVoucherList'],
    data: function () {    	
      	return {
            voucher         : {},
      		voucherList		: [],
            gv_num          : '',
            loadingTable    : false,
            showModal       : false  ,
      	}
  	},
    created(){
    },
    methods:{
        selectVoucher(val){
            console.log(val) 
            if(val){
                this.gv_num = val.gv_num
            }
        },
        locateVoucherNumber(){
            console.log(this.gv_num)
            if(this.gv_num == null || this.gv_num == ''){
                new Noty({
                    theme: 'relax',
                    type: 'error',
                    layout: 'topRight',
                    text: 'Please enter GV Number.',
                    timeout: 3000
                }).show();
                return
            }
            this.loadingTable = true
            let filter = {
                'gv_num' : this.gv_num
            }
            this.$API.Voucher.getVoucher(filter)
            .then(result => {
                var res = result.data
                if(res.success){  
                    this.voucher = res.voucher
                    this.voucherList = res.list
                }
                else{
                    this.voucher = {}
                    this.voucherList = []
                    new Noty({
                        theme: 'relax',
                        type: 'error',
                        layout: 'topRight',
                        text: 'GV Number not found.',
                        timeout: 3000
                    }).show();
                }
            })
            .catch(err => {
                console.log(err)
            })
            .then(_ => { 
                this.loadingTable = false
            })
        },
        printVoucher(){
            this.$htmlToPaper('voucherSampleForm');
        },
        updateVoucher(){
            window.location.href = this.$baseUrl+"/general-voucher/?record="+this.voucher.id;
        },
        finishVoucher(){
            let vm = this
            if(this.voucher){
                vm.$swal({
                    title: "Post Voucher",
                    text: "Are you sure you want to post voucher entries? This can't be undone",
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
                    console.log('result', result)
                    if (result.value) {
                        let voucher_id = this.voucher.id
                        var winFeature = 'location=no,toolbar=no,menubar=no,scrollbars=yes,resizable=yes';
                        window.open(this.$baseUrl + "/general-voucher/post-voucher?id="+voucher_id, 'null', winFeature);

                        location.reload();
                    }
                })

                
            }
            else{
                 new Noty({
                    theme: 'relax',
                    type: "error",
                    layout: 'topRight',
                    text: "No record to post.",
                    timeout: 3000
                }).show();
            }
        },
        getSummaries(param){
            console.log('getSummaries', param)
            const { columns, data } = param;
            const sums = [];

            columns.forEach((column, index) => {
                if (index === 0) {
                    sums[index] = 'Total';
                    return;
                }
                else{
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
                        sums[index] = ' ' + this.$nf.formatNumber(sumAmount, 2)
                    } else {
                        sums[index] = 'N/A';
                    }
                }

                
            });

            return sums;
        }

    },
    computed:{
        entryList(){
            let list = cloneDeep(this.voucherList)
            let account = []
            let totalAmt = 0

            let tCre = 0
            let tDeb = 0
            _forEach(list, rs =>{
                let acct = rs
                acct['table_key'] = rs.type + "_" + rs.particular_id
                let getInd = -1

                getInd = account.findIndex(ac => { return ac.particular_id == acct.particular_id})

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

            return account
        },
    }
  }
</script>
<style lang="scss">
    @import '~noty/src/noty.scss';
    .view-general-voucher{
        .el-button{
            width: 100%;
        }

        .voucher-sample-form{
            margin-top: 20px;

            h3{
                text-align: center;
                font-weight: bold;
            }
        }
        table.voucher-detail{
            margin-bottom: 30px;

            th.tlabel{
                padding: 2px 0px;
                width: 130px;
            }

            td.tvalue{
                padding: 2px 15px;
                width: 300px;
                border-bottom: 1px solid #000;
            }

        }
        
    }

</style>
