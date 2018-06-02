<template>
  <el-dialog title="Find Member" :visible.sync="dialogVisible"  width="45%" @close="closeModal">      
    <el-form label-width="120px" class="demo-dynamic" @submit.native.prevent>
        <el-form-item label="Member Name">
            <el-input @keyup.enter.native="getMember()" v-model="nameInput"></el-input><el-button @click="getMember()">Find</el-button>
      </el-form-item>
    </el-form>
    <el-table :data="tableData" style="width: 100%">
        <el-table-column label="Date" width="180">
          <template slot-scope="scope">
            <i class="el-icon-time"></i>
            <span style="margin-left: 10px">{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="Name" width="180">
          <template slot-scope="scope">
            <el-popover trigger="hover" placement="top">
              <p>Name: {{ scope.row.fullname }}</p>
            
              <div slot="reference" class="name-wrapper">
                <el-tag size="medium">{{ scope.row.fullname }}</el-tag>
              </div>
            </el-popover>
          </template>
        </el-table-column>
        <el-table-column label="Operations">
          <template slot-scope="scope">
            <el-button size="mini" @click="handleEdit(scope.index, scope.row)">Select</el-button>
           
          </template>
        </el-table-column>
      </el-table>

    <div>
        <el-button type="success" round>Success</el-button>
    </div>
 <span slot="footer" class="dialog-footer">
    <el-button @click="dialogVisible = false">Cancel</el-button>
    <el-button type="primary" @click="dialogVisible = false">Confirm</el-button>
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
    props: ['baseUrl'],
 
    data: function () {
      return {
               
               tableData: null,
               nameInput: "",
               dialogVisible : true
             }
    },
    methods: {

            getMember(){
        //start
            let data = new FormData()
            data.set('nameInput', this.nameInput)
            
            axios.post(this.baseUrl+'/member/get-member', data).then((result) => {
                      let res = result.data
                      let type = ""
                      let message = ""
                      console.log(res)
                      if(res.length > 0 ){
                      this.tableData = res
                      console.log("success")
                      }
                      else{
                        console.log("no result")
                      }            	

                      new Noty({
                          theme: 'relax',
                          type: type ,
                          layout: 'topRight',
                          text: message,
                          timeout: 2500
                      }).show();
                      
                    }).catch(function (error) {
                
                console.log(error);

                  if(error.response.status == 403)
                    location.reload()
                })
            //end
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
