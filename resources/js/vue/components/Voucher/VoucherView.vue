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
                        
                        <div class = "voucher-sample-form" v-loading = "loadingTable">
                            <h3>GENERAL VOUCHER</h3>
                            <table class = "voucher-detail">
                                <tr>
                                    <th class = "tlabel">Name: </th>
                                    <td class = "tvalue">{{ voucher.name }}</td>
                                    <td style="width:50px"></td>
                                    <th class = "tlabel">Date Transact: </th>
                                    <td class = "tvalue">{{ $df.formatDate(voucher.date_transact, "YYYY-MM-DD")}}</td>
                                </tr>
                                <tr>
                                    <th class = "tlabel">GV Number: </th>
                                    <td class = "tvalue">{{ voucher.gv_num }}</td>
                                </tr>
                            </table>
                            <el-table
                                :data="voucherList"
                                border striped
                                style="width: 100%"
                                height = "400px">
                                <el-table-column
                                    prop="particular.name"
                                    label="Description">
                                </el-table-column>
                                <el-table-column
                                    prop="debit"
                                    label="Debit">
                                    <template slot-scope="scope">
                                        {{ Number(scope.row.debit).toFixed(2) }}
                                    </template>
                                </el-table-column>
                                <el-table-column
                                    prop="credit"
                                    label="Credit">
                                    <template slot-scope="scope">
                                        {{ Number(scope.row.credit).toFixed(2) }}
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

export default {
    components: { AllVouchers },
    props: ['dataVoucherList'],
    data: function () {    	
      	return {
            voucher         : {},
      		voucherList		: [],
            gv_num          : '',
            loadingTable    : false,
            showModal       : false    
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
