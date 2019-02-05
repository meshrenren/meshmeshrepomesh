<template>
	<div class="view-general-voucher">
        <el-row :gutter="20">
            <el-col :span="6">
                <el-input type="text" v-model="gv_num" placeholder = "Enter GV Number" ></el-input>
            </el-col>
            <el-col :span="3">
                <el-button type = "primary" @click = "locateVoucherNumber">Locate</el-button>
            </el-col>
            <el-col :span="3">
                <el-button type = "default" @click = "printVoucher">Print</el-button>
            </el-col>
            <el-col :span="3">
                <el-button type = "success">Add</el-button>
            </el-col>
            <el-col :span="24">
                
                <div class = "voucher-sample-form">
                    <h3>VOUCHER</h3>
                    <el-table
                        :data="generalVoucherList"
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
                            prop="description"
                            label="Description">
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
                            prop="name"
                            label="Name">
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
    props: ['dataVoucherList'],
    data: function () {    	
      	return {
      		generalVoucherList		: this.dataVoucherList,
            gv_num                 : '',
            loadingTable            : false
      	}
  	},
    created(){
    },
    methods:{
        locateVoucherNumber(){
            let filter = {
                'gv_num' : this.gv_num
            }
            this.$API.Voucher.getVoucher(filter)
            .then(result => {
                var res = result.data
                console.log(res)
                this.generalVoucherList = res.data
            })
            .catch(err => {
                console.log(err)
            })
            .then(_ => { 
                loadingTable = false
            })
        },
        printVoucher(){

        }
    }
  }
</script>
<style lang="scss">
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
        
    }

</style>
