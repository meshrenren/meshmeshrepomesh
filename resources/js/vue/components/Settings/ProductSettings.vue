<template>
	<div class = "product-settings">
		<div class = "row">
			<div class = "col-md-12">
				<el-tabs tab-position="top" v-model="activeTab"  type = "border-card">

					<el-tab-pane label="Loan" name="loan-settings" lazy>
						<loan-account-settings 
						:page-data = "loanAccountData">
						</loan-account-settings>
					</el-tab-pane>
				</el-tabs>
			</div>
		</div>
	</div>
</template>

<script>
	import LoanAccountSettings from './ProductSettings/LoanAccountSettings'

export default {
	props: ['pageData'],
	components: {LoanAccountSettings},
	data(){
		let loanAccountData = {
			productList : this.pageData.loan.productList
		}
		return{
			activeTab		: 'loan-settings',
			loanAccountData : loanAccountData
		}
	}
}
</script>