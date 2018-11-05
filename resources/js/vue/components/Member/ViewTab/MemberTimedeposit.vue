<template>
	<div class = "member-timedeposit">
		<el-table :data="dataAccounts" style="width: 100%" stripe border>
            <el-table-column label="Account Number">
                <template slot-scope="scope">
                    <span style="margin-left: 10px">{{ scope.row.accountnumber }}</span>
                </template>
            </el-table-column>
            <el-table-column label="Account Name">
                <template slot-scope="scope">
                    <span>{{ scope.row.member.fullname }}</span>
                </template>
            </el-table-column>
            <el-table-column label="Savings Product">
                <template slot-scope="scope">
                    <span style="margin-left: 10px">{{ scope.row.product.description }}</span>
                </template>
            </el-table-column>
            <el-table-column label="Term (days)">
                <template slot-scope="scope">
                    <span style="margin-left: 10px">{{ scope.row.term }}</span>
                </template>
            </el-table-column>
            <el-table-column label="Amount">
                <template slot-scope="scope">
                    <span style="margin-left: 10px">{{ $nf.numberFixed(scope.row.term, 2) }}</span>
                </template>
            </el-table-column>
            <el-table-column label="Date Open">
                <template slot-scope="scope">
                    <span style="margin-left: 10px">{{ $df.formatDate(scope.row.date_created, "MMM D, YYYY") }}</span>
                </template>
            </el-table-column>
            <el-table-column label="Maturity Date">
                <template slot-scope="scope">
                    <span style="margin-left: 10px">{{ $df.formatDate(scope.row.maturity_date, "MMM D, YYYY") }}</span>
                </template>
            </el-table-column>
        </el-table>
        <span v-if = "dataAccounts == null || dataAccounts.length == 0">No time deposit accounts.</span>
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