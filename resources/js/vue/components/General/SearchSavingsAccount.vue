<template>
    <el-dialog title="Find Account" :visible.sync="dialogVisible"  width="55%" top = "20px" @close="closeModal">  
        <el-form label-width="0" class="demo-dynamic" @submit.native.prevent>
            <el-form-item label="">
                <el-input v-model="nameInput" autofocus>
                    <!-- <el-button slot="append" type = "primary" @click="getAccount()">Find Member</el-button> -->
                </el-input>
            </el-form-item>
        </el-form>

        <el-table :data="savingsList" style="width: 100%" height="400" stripe border v-loading = "loadingTable">
            <el-table-column label="ID" width="180">
                <template slot-scope="scope">
                    <span style="margin-left: 10px">{{ scope.row.account_no }}</span>
                </template>
            </el-table-column>
            <el-table-column label="Name"  width="180">
                <template slot-scope="scope">
                    <el-popover trigger="hover" placement="top" v-if = "scope.row.member">
                        <p>Name: {{ scope.row.member.fullname }}</p>
                    
                        <div slot="reference" class="name-wrapper">
                            <el-tag size="medium">{{ scope.row.member.fullname }}</el-tag>
                        </div>
                    </el-popover>
                </template>
            </el-table-column>
            <el-table-column label="Savings Product" width="180">
                <template slot-scope="scope">
                    <span style="margin-left: 10px">{{ scope.row.product.description }}</span>
                </template>
            </el-table-column>
            <el-table-column label="Balance" width="150">
                <template slot-scope="scope">
                    <span style="margin-left: 10px">{{ scope.row.balance }}</span>
                </template>
            </el-table-column>
            <el-table-column label="Action">
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
    import Noty from 'noty'
    import {dialogComponent} from '../../mixins/dialogComponent.js'


export default {
    props: ['baseUrl', 'showModal'],
 
    data: function () {
      return {               
               tableData: null,
               nameInput: "",
               dialogVisible : true,
               loadingTable : false,
            }
    },
    created(){
        this.getAccount()
    },
    computed: {
        savingsList(){            
            let datalist = this.tableData
            let filterKey = this.nameInput

            if (filterKey) {
                if(datalist){
                    datalist = datalist.filter(function (row) {
                        return row.member && String(row.member.fullname).toLowerCase().indexOf(filterKey) > -1
                    })
                }
            }

            return datalist
        }
    },
    methods: {
        getAccount(){
            this.loadingTable = true

            this.$API.Savings.getAccounts()
            .then(result => {
                let res = result.data
                this.tableData = res
            })
            .catch(err => {
                console.log(err)
            })
            .then(_ => { 
                this.loadingTable = false
            })
        },

        handleEdit(index, row){
            this.$emit('select', row)
            this.dialogVisible = false
        }
    },
    watch: {
        showModal : function(val){     
            this.dialogVisible = val
        }
    },
    mixins: [dialogComponent],
  
}
</script>
