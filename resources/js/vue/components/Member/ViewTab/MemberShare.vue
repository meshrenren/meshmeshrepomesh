<template>
	<div class = "member-share">
		<el-table :data="dataAccounts" style="width: 100%" stripe border v-loading = "loadingTable">

            <el-table-column label="Account Number" 
                prop = "accountnumber">
            </el-table-column>

            <el-table-column label="Account Name">
                <template slot-scope="scope">
                    <span>{{ memberData.last_name }}, {{ memberData.first_name }} {{ memberData.middle_name }}</span>
                </template>
            </el-table-column>

            <el-table-column label="Share Product" 
                prop = "product.name">
            </el-table-column>

            <el-table-column label="Balance">
                <template slot-scope="scope">
                    <span>{{ $nf.numberFixed(scope.row.balance, 2) }}</span>
                </template>
            </el-table-column>

            <el-table-column label="Action">
                <template slot-scope="scope">
                    <el-button size="mini" @click="selectAccount(scope.index, scope.row)">View Transaction</el-button>
                </template>
            </el-table-column>

        </el-table>

        <el-dialog title="Share Transaction" top = "10px" v-if="dialogVisible"  :visible.sync="dialogVisible" width="80%" @close = "dialogVisible = false">
            <transaction-list 
                :account-selected = "selectAccountDetails"
                :account-transaction-list = "selectAccountTrans"
                account-type = "Share"> 
            </transaction-list>
        </el-dialog>
	</div>
</template>
<script>
import cloneDeep from 'lodash/cloneDeep' 

import TransactionList from '../../General/TransactionList.vue' 

export default {
	props: ['member', 'canEdit'],
	data: function () {
		return{
			memberData              : this.member,
            dataAccounts            : [],
            loadingTable            : false,
            selectAccountDetails    : {},
            selectAccountTrans      : [],
            dialogVisible           : false
        }
    },
    components: { TransactionList },
    created(){
        this.getAccount()
    },
    methods:{
        getAccount(balance){
            this.loadingTable = true

            let type = ['SHARE']
            this.$API.Member.getAccounts(type, this.memberData.id, "")
            .then(result => {
                var res = result.data
                this.dataAccounts = res.shareAccounts
            })
            .catch(err => {
                console.log(err)
            })
            .then(_ => { 
                this.loadingTable = false
            })
        },

        selectAccount(index, data){
            this.selectAccountDetails = cloneDeep(data)
            this.selectAccountDetails['member'] = {}
            this.selectAccountDetails['member']['fullname'] = this.memberData.last_name + " " + this.memberData.first_name + " " + this.memberData.middle_name
            this.getTransaction(this.selectAccountDetails.accountnumber)
        },
        getTransaction(account_no){
            this.loadingTable = true

            this.$API.Share.getTransaction(account_no)
            .then(result => {
                var res = result.data
                if(res.length > 0 ){
                    this.selectAccountTrans = res
                }
                this.dialogVisible = true
            })
            .catch(err => {
                console.log(err)
            })
            .then(_ => { 
                this.loadingTable = false
            })
        },
    }
}
</script>