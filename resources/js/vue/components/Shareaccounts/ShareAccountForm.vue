<template>
    <div class="share-account-create" v-loading  ="pageLoading">
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
                                    <span>{{ $nf.formatNumber(scope.row.balance, 2) }}</span>
                                </template>
                            </el-table-column>
                        </el-table>
                    </el-col>
                    <el-col :span="12">
                        <h3>Add New Account</h3>
                        <el-form  label-width="120px" :model="shareaccount" :rules = "ruleAccount" ref="shareaccount">
                            <el-form-item label="Name" prop="fk_memid">
                                <el-input v-model="fullname" >
                                    <el-button slot="append" type = "primary" @click="showModal = true">Find Member</el-button>
                                </el-input>
                            </el-form-item>

                            <el-form-item label="Customer ID">
                                <el-input v-model="shareaccount.fk_memid" :disabled = "true"></el-input>
                            </el-form-item>

                            <el-form-item label="Share Product" prop="shareProduct" ref="shareProduct">
                                <el-select v-model = "shareaccount.fk_share_product" prop="shareProduct" :disabled = "true"  placeholder="Please Select Share Product">
                                    <el-option
                                      v-for="product in shares" :key="Number(product.id)" :label="product.name" :value="Number(product.id)">
                                    </el-option>
                                </el-select>
                            </el-form-item>
                            
                            <el-form-item label="No. Of Shares" prop = "no_of_shares">
                              <el-input v-model="shareaccount.no_of_shares" :value="shareaccount.no_of_shares"></el-input>
                            </el-form-item>
                            
                            <el-form-item>
                                <el-button class = "pull-right" @click="onSubmit()" type="primary" >Save New Account</el-button>
                            </el-form-item>

                        </el-form>
                    </el-col>
                </el-row>
            </div>
            <search-member :base-url="baseUrl" :show-modal = "showModal"  @select="populateField" @close = "showModal = false" >
            </search-member>
        </div>
    </div>
</template>

<style lang="scss">
  	@import '../../assets/member.scss';
  	@import '~noty/src/noty.scss';


</style>


<script>
window.noty = require('noty')
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
            showModal       : true,
            isWithDep       : false,
            accountList     : this.shareAccountList,
            accountFilter   : '',
            ruleAccount     : [],
            pageLoading     : false
        }
    },
    created(){
        this.shareaccount.fk_share_product = 1
        this.ruleAccount = {
            fk_memid : [{ required: true, message: 'Please select member. Just click "Find Member" button.', trigger: 'change' }],
            no_of_shares : [{ required: true, message: 'No Of Shares cannot be blank.', trigger: 'change' }],
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
            let vm = this
            this.$refs.shareaccount.validate((valid) => {
                if (valid) {
                    vm.$swal({
                        title: 'Create Share Account?',
                        text: "Are you sure you want to save this account? This action cannot be undone.",
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
                            vm.createAccount()
                        }
                   })
                } else {
                    return false;
                }
            })
        },

        createAccount(){
            this.pageLoading = true
            this.$API.Share.createAccount(this.shareaccount)
            .then(result => {
                var res = result.data
                let message = ""
                let type = ""
                if(res.success){
                    type = "success"
                    message = "New share account successfully created."
                    location.reload()
                }
                else{
                    if(res.error == 'HAS_ACCOUNT'){
                        let getProduct = this.shareProduct.find(prod => { return Number(prod.id) == Number(this.shareaccount.fk_share_product) } )
                        console.log(getProduct)
                        message = this.fullname + " already has "+ getProduct.name + " account."

                    }
                    else{
                        message = "Share account not successfully created. Please try again or contact administrator."
                        //location.reload()
                    }
                    type = "error"
                }

                new Noty({
                    theme: 'relax',
                    type: type,
                    layout: 'topRight',
                    text: message,
                    timeout: 2500
                }).show()
            })
            .catch(err => {
                console.log(err)
            })
            .then(_ => { 
                this.pageLoading = false
            })

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
