<template>
    <div class="share-account-create">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Share Account</h3>
            </div>

            <div class = "box-body">
                <el-row :gutter = "20">         
                    <el-col :span="12">
                        <el-input v-model="accountFilter" autofocus placeholder = "Search Account">
                            <!-- <el-button slot="append" type = "primary" @click="getMember()">Find Member</el-button> -->
                        </el-input>
                        <el-table :data="accountListData"  height="450" stripe border style = "margin-top:10px;">
                            <el-table-column label="Account Number">
                                <template slot-scope="scope">
                                    <span>{{ scope.row.accountnumber }}</span>
                                </template>
                            </el-table-column>
                            <el-table-column label="Account Name">
                                <template slot-scope="scope">
                                    <span>{{ scope.row.account_name }}</span>
                                </template>
                            </el-table-column>
                            <el-table-column label="Balance">
                                <template slot-scope="scope">
                                    <span>{{ $nf.numberFixed(scope.row.balance, 2) }}</span>
                                </template>
                            </el-table-column>
                        </el-table>
                    </el-col>
                    <el-col :span="12">
                        <h3>Add New Account</h3>
                        <el-form  label-width="120px" :model="shareaccount" ref="shareaccount">
                            <el-form-item label="Name">
                                <el-input v-model="fullname" >
                                    <el-button slot="append" type = "primary" @click="showModal = true">Find Member</el-button>
                                </el-input>
                            </el-form-item>

                            <el-form-item label="Customer ID">
                                <el-input v-model="shareaccount.fk_memid" ></el-input>
                            </el-form-item>

                            <el-form-item label="Share Product" prop="shareProduct" ref="shareProduct">
                                <el-select v-model = "shareaccount.fk_share_product" prop="shareProduct"  placeholder="Please Select Share Product">
                                    <el-option
                                      v-for="product in shares" :key="product.id" :label="product.name" :value="product.id">
                                    </el-option>
                                </el-select>
                            </el-form-item>
                            
                            <el-form-item label="No. Of Shares">
                              <el-input v-model="shareaccount.no_of_shares" :value="shareaccount.no_of_shares"></el-input>
                            </el-form-item>

                            
                            <el-form-item label="" prop="shareProduct" ref="shareProduct">
                                <el-checkbox @change="toggleDeposit()" v-model="shareaccount.isWithDeposit" prop="shareProduct" label="Is With Deposit" name="type"></el-checkbox>
                            </el-form-item>
                            
                            <el-form-item label="Amount" v-if="isWithDep">
                              <el-input v-model="shareaccount.Deposit" type="number"></el-input>
                            </el-form-item>
                            
                            <el-form-item>
                                <el-button @click="onSubmit()" type="primary" >Creates</el-button>
                                <el-button>Cancel</el-button>
                            </el-form-item>

                          </el-form>
                    </el-col>
                </el-row>
            </div>
        </div>
        <search-member :base-url="baseUrl" v-if = "showModal" @select="populateField" @close = "showModal = false" >
        </search-member>
    </div>
</template>

<style lang="scss">
  	@import '../../assets/member.scss';
  	@import '~noty/src/noty.scss';


</style>


<script>
window.noty = require('noty');
    import axios from 'axios'
    import Noty from 'noty'

    import SearchMember from '../General/SearchMember.vue'

    import cloneDeep from 'lodash/cloneDeep'    
    import _forEach from 'lodash/forEach'

    
export default {
    props: ['baseUrl', 'shareProduct', 'shareAccountDetails', 'shareAccountList'],
    components: {
      SearchMember
    },
    data: function () {
        let account  = cloneDeep(this.shareAccountDetails)

        return {
            shares          : this.shareProduct,
            shareaccount    : account, //this is our form model
            id              : "",
            fullname        : "",
            dialogVisible   : false,
            showModal       :false,
            isWithDep       : false,
            accountList     : this.shareAccountList,
            accountFilter   : ''
        }
    },
    computed:{
        accountListData(){
            let datalist = this.accountList
            let filterKey = this.accountFilter

            _forEach(datalist, function(element, index) {
                if(element.member){
                    element.account_name = element.member.last_name + ', ' + element.member.first_name + ' ' + element.member.middle_name
                }
                
            })

            if (filterKey) {
                if(datalist){
                    datalist = datalist.filter(function (row) {
                        return  String(row.account_name).toLowerCase().indexOf(filterKey) > -1
                    })
                }
            }

            return datalist
        }
    },
    methods: {

    populateField(row){
      console.log("populateFields",row)
      this.id = row.id
      this.fullname = row.fullname
      this.shareaccount.fk_memid = row.id;
    },

     toggleDeposit(){
        console.log(this.shareaccount.isWithDeposit);
        this.shareaccount.Deposit = 0;
        this.isWithDep = this.shareaccount.isWithDeposit;
        
     },


     onSubmit()
     {
       //first get the form data
       let dataSubmitted = new FormData();

       dataSubmitted.set('shareaccount', JSON.stringify(this.shareaccount));

       axios.post(this.baseUrl+'/shareaccount/createaccount', dataSubmitted).then((result)=>{
          let res  = result.data;
          console.log(res);
       });


     }



    }
  
}
</script>
<style lang="scss">
    @import '../../assets/site.scss';
    @import '~noty/src/noty.scss';

    .share-account-create{

        .el-select{
            width: 100%;
        }
    }
    
</style>
