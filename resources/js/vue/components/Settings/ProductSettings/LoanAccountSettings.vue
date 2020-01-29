<template>
	<div class = "loan-account-settings">
		<div class = "row">
			<div class = "col-md-12">
				<el-table
					border
					:data="productList"
					fit>
					<el-table-column
						label="Name"
						prop="product_name">
					</el-table-column>
		            <el-table-column label="Interest Rate">
		                <template slot-scope="scope">
		                    <span>{{ $nf.formatNumber(scope.row.int_rate) }}</span>
		                </template>
		            </el-table-column>
		            <el-table-column label="Prepaid Interest">
		                <template slot-scope="scope">
		                    <span>{{ $nf.formatNumber(scope.row.prepaid_interest) }}</span>
		                </template>
		            </el-table-column>
		            <el-table-column label="Redemption Insurance">
		                <template slot-scope="scope">
		                    <span>{{ $nf.formatNumber(scope.row.redemption_insurance) }}</span>
		                </template>
		            </el-table-column>
		            <el-table-column label="Notary fee">
		                <template slot-scope="scope">
		                    <span>{{ $nf.formatNumber(scope.row.notary_fee) }}</span>
		                </template>
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
					<el-table-column label="Action" width="100">
		                <template slot-scope="scope">
		                    <el-button size="mini" @click="showProduct(scope.row)">Select</el-button>
		                </template>
		            </el-table-column>
				</el-table>
			</div>
		</div>
		<dialog-modal 
	  		title-header = ""
	  		width = "50%"
            v-if="showFormModal"
            :visible.sync="showFormModal"
            @close="showFormModal = false">
            <loan-product-form
            	:loan-data = "selectedProduct"
            	@saveproduct = "updateList"
            	>
            </loan-product-form>
        </dialog-modal>
	</div>
</template>

<script>
	window.noty = require('noty')
	import Noty from 'noty'

    import cloneDeep from 'lodash/cloneDeep'    
    import _forEach from 'lodash/forEach'

    import LoanProductForm from './LoanProductForm.vue'

export default {
	props: ['pageData'],
	components: { LoanProductForm },
	data(){
		return{
			loanProductList	: this.pageData.productList,
			showFormModal	: false,
			selectedProduct	: null
		}
	},
	computed:{
		productList(){
			let list = cloneDeep(this.loanProductList)
			_forEach(list => {

			})

			return list
		}
	},
	methods:{
		showProduct(data){
			console.log("data", data)
			this.selectedProduct = data
			this.showFormModal = true
		},
		updateList(data){

		}
	}
}
</script>