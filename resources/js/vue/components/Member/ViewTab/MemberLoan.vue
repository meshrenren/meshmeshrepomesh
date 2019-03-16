<template>
	<div class = "member-loan">
		<el-table :data="dataAccounts" style="width: 100%" stripe border v-loading = "loadingTable">

            <el-table-column label="Account Number" 
                prop = "account_no">
            </el-table-column>

            <el-table-column label="Account Name">
                <template slot-scope="scope">
                    <span>{{ memberData.last_name }}, {{ memberData.first_name }} {{ memberData.middle_name }}</span>
                </template>
            </el-table-column>

            <el-table-column label="Loan Type" 
                prop = "product.product_name">
            </el-table-column>

            <el-table-column label="Principal">
                <template slot-scope="scope">
                    <span>{{ $nf.numberFixed(scope.row.principal, 2) }}</span>
                </template>
            </el-table-column>

            <el-table-column label="Balance">
                <template slot-scope="scope">
                    <span>{{ $nf.numberFixed(scope.row.principal_balance, 2) }}</span>
                </template>
            </el-table-column>

            <el-table-column label="Maturity Date">
                <template slot-scope="scope">
                    <span>{{ $df.formatDate(scope.row.maturity_date, "MMM D, YYYY") }}</span>
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
			memberData : this.member,
            dataAccounts    : [],
            loadingTable     : false
        }
    },
    created(){
        this.getAccount()
    },
    methods:{
        getAccount(balance){
            this.loadingTable = true

            let type = ['LOAN']
            this.$API.Member.getAccounts(type, this.memberData.id, "")
            .then(result => {
                var res = result.data
                this.dataAccounts = res.loanAccounts
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