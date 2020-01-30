<template>
	<div class="loan-product-form" v-loading = "pageLoading">
    	<el-row :gutter="20">

    		<el-col :span="24">
    			<div class = "box-content">
        			<el-form label-position="right" label-width="180px" :model="loanProductForm">
        				<el-form-item label="Loan Type" prop="product_name">
						    <el-input v-model="loanProductForm.product_name" :disabled = "true"></el-input>
						</el-form-item>
						<el-form-item label="Interest Rate" prop="int_rate">
						    <el-input type = "number" v-model="loanProductForm.int_rate">
						    	<template slot="append">%</template>
						    </el-input>
						</el-form-item>
						<el-form-item label="Prepaid Interest" prop="prepaid_interest">
						    <el-input type = "number" v-model="loanProductForm.prepaid_interest">
						    	<template slot="append">%</template>
						    </el-input>
						</el-form-item>
						<el-form-item label="Redemption Insurance" prop="redemption_insurance">
						    <el-input type = "number" v-model="loanProductForm.redemption_insurance">
						    	<template slot="append">%</template>
						    </el-input>
						</el-form-item>
						<el-form-item label="Notary Fee" prop="notary_fee">
						    <el-input type = "number" v-model="loanProductForm.notary_fee">
						    	<template slot="append">Php</template>
						    </el-input>
						</el-form-item>
						<el-button type = "primary"  @click="validateProduct">Update</el-button>
						<el-button type = "default"  @click="closeModal">Cancel</el-button>
        			</el-form>
        		</div>
        	</el-col>
        </el-row>
	</div>
</template>

<style lang="scss">
  	@import '../../../assets/site.scss';
  	@import '~noty/src/noty.scss';
</style>
<script>
window.noty = require('noty')
import Noty from 'noty'

import cloneDeep from 'lodash/cloneDeep'   

export default { 
	props: {
		loanData : {
			type: Object,
			required: true
		},
	},
	data(){
		let form = this.loanData
		return {
			loanProductForm : form,
			pageLoading 	: false
		}
	},
	created(){
		this.setProduct()
	},
	methods:{
		updateProduct(){
			console.log("Here")
			let getProd = cloneDeep(this.loanProductForm)
			getProd.prepaid_interest = getProd.prepaid_interest ? getProd.prepaid_interest / 100 : 0
			getProd.redemption_insurance = getProd.redemption_insurance ? getProd.redemption_insurance / 100 : 0

			this.pageLoading = true
			this.$API.Settings.saveLoanProduct(getProd)
    		.then(result => {
    			let res = result.data

    			this.$emit('saveproduct', res.data)
    		})
    		.catch(err => {
    			console.log(err)
    			if (err.response && err.response.status !== 500) {
					new Noty({
                        theme: 'relax',
                        type: 'error',
                        layout: 'topRight',
                        text: 'An error occured. Please try again or contact administrator',
                        timeout: 5000
                    }).show();
				}
			})
			.then(_ => { 
				this.pageLoading = false
			})
		},
		validateProduct(){
			this.$swal({
              	title: 'Update Loan Type',
              	text: "Are you sure you want to update loan type? This action will affect the loan account.",
              	type: 'warning',
              	showCancelButton: true,
              	cancelButtonColor: '#d33',
              	confirmButtonText: 'Proceed',
              	focusConfirm: false,
             	focusCancel: true,
              	cancelButtonText: 'Cancel',
              	reverseButtons: true,
              	customClass: {
			    	container: 'loan-product-form-swal'
			  	}
            }).then(result => {
            	if (result.value) {
            		this.updateProduct()
            	}
            })
		},
		setProduct(){
			this.loanProductForm.redemption_insurance = this.loanProductForm.redemption_insurance ? this.loanProductForm.redemption_insurance * 100 : 0
			this.loanProductForm.prepaid_interest = this.loanProductForm.prepaid_interest ? this.loanProductForm.prepaid_interest * 100 : 0
		},
		closeModal(){
			this.$emit('closeloanproduct')
		}
	},

}
</script>
<style lang="scss">
.loan-product-form-swal{
	z-index: 3000;
}

</style>