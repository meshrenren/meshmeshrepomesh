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

            <el-table-column label="Savings Product" 
                prop = "product.name">
            </el-table-column>

            <el-table-column label="Balance">
                <template slot-scope="scope">
                    <span>{{ $nf.numberFixed(scope.row.balance, 2) }}</span>
                </template>
            </el-table-column>

        </el-table>
	</div>
</template>
<script>


export default {
	props: ['member', 'canEdit'],
	data: function () {
		return{
			memberData       : this.member,
            dataAccounts     : [],
            loadingTable     : false
        }
    },
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
        }
    }
}
</script>