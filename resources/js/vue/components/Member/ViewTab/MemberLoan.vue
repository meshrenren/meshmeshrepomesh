<template>
	<div class = "member-loan">
		<el-table :data="dataAccounts" style="width: 100%" stripe border>
            <el-table-column label="Account Number">
                <template slot-scope="scope">
                    <span style="margin-left: 10px">{{ scope.row.account_no }}</span>
                </template>
            </el-table-column>
            <el-table-column label="Account Name">
                <template slot-scope="scope">
                    <span>{{ scope.row.member.fullname }}</span>
                </template>
            </el-table-column>
            <el-table-column label="Loan Type">
                <template slot-scope="scope">
                    <span style="margin-left: 10px">{{ scope.row.product.product_name }}</span>
                </template>
            </el-table-column>
            <el-table-column label="Principal">
                <template slot-scope="scope">
                    <span style="margin-left: 10px">{{ $nf.numberFixed(scope.row.principal, 2) }}</span>
                </template>
            </el-table-column>
            <el-table-column label="Balance">
                <template slot-scope="scope">
                    <span style="margin-left: 10px">{{ $nf.numberFixed(scope.row.principal_balance, 2) }}</span>
                </template>
            </el-table-column>
            <el-table-column label="Maturity Date">
                <template slot-scope="scope">
                    <span style="margin-left: 10px">{{ $df.formatDate(scope.row.maturity_date, "MMM D, YYYY") }}</span>
                </template>
            </el-table-column>
        </el-table>
        <span v-if = "dataAccounts == null || dataAccounts.length == 0">No loan accounts.</span>
	</div>
</template>
<script>


export default {
	props: ['member', 'canEdit', 'dataAccounts'],
	data: function () {
		return{
			memberData : this.member
		}
	},
}
</script>