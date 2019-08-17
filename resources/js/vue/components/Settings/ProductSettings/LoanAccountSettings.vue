<template>
	<div class = "loan-account-settings">
		<div class = "row">
			<div class = "col-md-12">
				<el-table
					border
					:data="loanProductList"
					fit>
					<el-table-column
						label="Name"
						prop="product_name">
					</el-table-column>
					<el-table-column
						label="Term Min - Max">
						<template slot-scope="scope">
							<span>{{ scope.row.term_min }} - {{ scope.row.term_max }}</span>
						</template>
					</el-table-column>
					<el-table-column
						label="Amount Min - Max">
						<template slot-scope="scope">
							<span>{{ scope.row.min_amount }} - {{ scope.row.max_amount }}</span>
						</template>
					</el-table-column>
				</el-table>
			</div>
		</div>
	</div>
</template>

<script>

export default {
	props: ['pageData'],
	data(){
		return{
			loanProductList	: this.pageData.productList
		}
	}
}
</script>