<template>
	<div class = "member-timedeposit">
		<el-table :data="dataAccounts" style="width: 100%" stripe border v-loading = "loadingTable">

            <el-table-column label="Account Number" 
                prop = "accountnumber">
            </el-table-column>

            <el-table-column label="Account Name">
                <template slot-scope="scope">
                    <span v-if = "scope.row.type == 'Group'">{{ scope.row.account_name }}</span>
                    <span v-else>{{ memberData.last_name }}, {{ memberData.first_name }} {{ memberData.middle_name }}</span>
                </template>
            </el-table-column>

            <el-table-column label="Savings Product" 
                prop = "product.description">
            </el-table-column>

            <el-table-column label="Term (days)" 
                prop = "term">
            </el-table-column>

            <el-table-column label="Amount">
                <template slot-scope="scope">
                    <span>{{ $nf.numberFixed(scope.row.term, 2) }}</span>
                </template>
            </el-table-column>

            <el-table-column label="Date Open">
                <template slot-scope="scope">
                    <span>{{ $df.formatDate(scope.row.date_created, "MMM D, YYYY") }}</span>
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

            let type = ['TIME_DEPOSIT']
            this.$API.Member.getAccounts(type, this.memberData.id, "")
            .then(result => {
                var res = result.data
                this.dataAccounts = res.timedepositAccounts
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