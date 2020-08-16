<template>
    <el-dialog title="All Voucher" :visible.sync="dialogVisible"  width="45%" top = "20px" @close="closeModal">      
        <el-form label-width="0" class="demo-dynamic" @submit.native.prevent>
            <el-form-item label="">
                <el-input v-model="nameInput" placeholder = "Search Enter GV Number" autofocus>
                    <!-- <el-button slot="append" type = "primary" @click="getMember()">Find Member</el-button> -->
                </el-input>
            </el-form-item>
        </el-form>
        <el-table :data="voucherList" style="width: 100%" height="400" stripe border v-loading = "loadingTable">
            <el-table-column label="GV Number" width="180">
                <template slot-scope="scope">
                    <span style="margin-left: 10px">{{ scope.row.gv_num }}</span>
                </template>
            </el-table-column>
            <el-table-column label="Name" >
                <template slot-scope="scope">
                    <p><b>{{ scope.row.name }}</b></p>
                </template>
            </el-table-column>
            <el-table-column label="Date Transact" >
                <template slot-scope="scope">
                    <span style="margin-left: 10px">{{ $df.formatDate(scope.row.date_transact, "YYYY-MM-DD")}}</span>
                </template>
            </el-table-column>
            <el-table-column label="Action" width="100">
                <template slot-scope="scope">
                    <el-button size="mini" @click="handleEdit(scope.index, scope.row)">Select</el-button>
                </template>
            </el-table-column>
        </el-table>
        <span slot="footer" class="dialog-footer">
            <el-button @click="dialogVisible = false">Cancel</el-button>
        </span>
    </el-dialog>
</template>


<style lang="scss">
  	@import '../../assets/member.scss';
  	@import '~noty/src/noty.scss';

</style>


<script>
window.noty = require('noty');
    import axios from 'axios'
    import Noty from 'noty'
    import {dialogComponent} from '../../mixins/dialogComponent.js'


export default {
    props: ["showModal"],
    data: function () {
        return {
            tableData       : [],
            nameInput       : "",
            dialogVisible   : this.showModal,
            loadingTable    : true
        }
    },
    created(){
        
    },
    mounted(){
        setTimeout(() => {
            this.getAllVoucher()
        }, 1000);
        
    },
    computed: {
        voucherList(){            
            let datalist = this.tableData
            let filterKey = this.nameInput

            if (filterKey) {
                if(datalist){
                    datalist = datalist.filter(function (row) {
                        return String(row.gv_num).toLowerCase().indexOf(filterKey) > -1
                    })
                }
            }

            return datalist
        }
    },
    methods: {
        getAllVoucher(){
            //start   
            this.loadingTable = true
            this.$API.Voucher.getAllVouchers()
            .then(result => {
                var res = result.data
                this.tableData = res.data
            })
            .catch(err => {
                console.log(err)
            })
            .then(_ => { 
                this.loadingTable = false
            })
        },
        handleEdit(index, row){
             // console.log(row.id + '|' + row.fullname)
            this.$emit('select', row)
            this.dialogVisible = false
        }
    },
    mixins: [dialogComponent],
    watch: {
        showModal : function(val){     
            this.dialogVisible = val
        }
    }
  
}
</script>
