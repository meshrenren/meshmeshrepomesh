<template>
	<div class = "member-savings">
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
            <el-table-column label="Savings Product">
                <template slot-scope="scope">
                    <span style="margin-left: 10px">{{ scope.row.product.description }}</span>
                </template>
            </el-table-column>
            <el-table-column label="Balance">
                <template slot-scope="scope">
                    <span style="margin-left: 10px">{{ $nf.numberFixed(scope.row.balance, 2) }}</span>
                </template>
            </el-table-column>
        </el-table>
        <span v-if = "dataAccounts == null || dataAccounts.length == 0">No savings accounts.</span>
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
	methods:{
		getBalance(balance){
			console.log(balance)
			let num = Number(balance).toFixed(2) 
			return num
		}
	}
}
</script>