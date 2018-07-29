<template>
    <el-dialog title="Find Member" :visible.sync="dialogVisible"  width="45%" top = "20px" @close="closeModal">      
        <el-form label-width="0" class="demo-dynamic" @submit.native.prevent>
            <el-form-item label="">
                <el-input v-model="nameInput" autofocus>
                    <!-- <el-button slot="append" type = "primary" @click="getMember()">Find Member</el-button> -->
                </el-input>
            </el-form-item>
        </el-form>
        <el-table :data="memberList" style="width: 100%" height="400" stripe border v-loading = "isGettingMember">
            <el-table-column label="ID" width="180">
                <template slot-scope="scope">
                    <span style="margin-left: 10px">{{ scope.row.id }}</span>
                </template>
            </el-table-column>
            <el-table-column label="Name" >
                <template slot-scope="scope">
                    <el-popover trigger="hover" placement="top">
                        <p>Name: {{ scope.row.fullname }}</p>
                    
                        <div slot="reference" class="name-wrapper">
                            <el-tag size="medium">{{ scope.row.fullname }}</el-tag>
                        </div>
                    </el-popover>
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
    props: [],
 
    data: function () {
        return {
            tableData       : null,
            nameInput       : "",
            dialogVisible   : true,
            isGettingMember : true
        }
    },
    created(){
        this.getMember()
    },
    computed: {
        memberList(){            
            let datalist = this.tableData
            let filterKey = this.nameInput

            if (filterKey) {
                if(datalist){
                    datalist = datalist.filter(function (row) {
                        return String(row.fullname).toLowerCase().indexOf(filterKey) > -1
                    })
                }
            }

            return datalist
        }
    },
    methods: {
        getMember(){
            //start
            let data = new FormData()
            data.set('nameInput', this.nameInput)
            
            axios.post(this.$baseUrl+'/member/get-member', data).then((result) => {
                let res = result.data
                let type = ""
                let message = ""
                this.tableData = res
                this.isGettingMember = false
            }).catch(function (error) {
                console.log(error);
                if(error.response.status == 403)
                    location.reload()
            })
        },

        handleEdit(index, row){
             // console.log(row.id + '|' + row.fullname)
            this.$emit('select', row)
            this.dialogVisible = false
        }
    },

    mixins: [dialogComponent],
  
}
</script>
