<template>
	<div class = "member-share">
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
                    <span style="margin-left: 10px">{{ scope.row.product.name }}</span>
                </template>
            </el-table-column>
            <el-table-column label="Balance">
                <template slot-scope="scope">
                    <span style="margin-left: 10px">{{ getBalance(scope.row.balance) }}</span>
                </template>
            </el-table-column>
        </el-table>
        <span v-if = "dataAccounts == null || dataAccounts.length == 0">No share accounts.</span>
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