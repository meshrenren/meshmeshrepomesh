<template>
	<div class="time-deposit-form">
		<div class="box box-info">
            <div class="box-header with-border">
              	<h3 class="box-title">Time Deposit Account</h3>
            </div>
	        <div class = "box-body">
	        	<el-form label-position="right" label-width="180px" :model="memberDetails">
	        		<el-row :gutter = "20">
	        			<el-col :span="18">
						  	<el-form-item label="Member ID">
						    	<el-input v-model="memberDetails.id" :disabled = "true"></el-input>
						  	</el-form-item>
						</el-col>
	        			<el-col :span="18">
						  	<el-form-item label="Member Name">
						    	<el-input v-model="memberDetails.fullname" :disabled = "true"></el-input>
						  	</el-form-item>
						</el-col>
	        			<el-col :span="6">
						  	<el-button type = "info" @click="showSearchModal = true">Search Member</el-button>
						</el-col>
					</el-row>
				</el-form>
				<el-form label-position="right" label-width="180px" :model="tdAccountDetails" :rules = "ruleAccount" ref = "tdAccountDetails">
					<el-row :gutter = "20">
	        			<el-col :span="12">
						  	<el-form-item label="Product" prop = "fk_td_product">
						    	<el-select v-model="tdAccountDetails.fk_td_product" placeholder="Select" @change = "productChange">
								    <el-option
								      v-for="item in tdProductSelect"
								      :key="item.value"
								      :label="item.label"
								      :value="item.value">
								    </el-option>
								</el-select>
						  	</el-form-item>
						  	<el-form-item label="Amount" prop = "amount">
						    	<el-input-number v-model="tdAccountDetails.amount" controls-position="right" :min="1" @change = "amountChange"></el-input-number>
						  	</el-form-item>
						  	<el-form-item label="Term (Days)" prop = "term">
						    	<el-input-number v-model="tdAccountDetails.term" controls-position="right" :min="1" @change = "termChange"></el-input-number>
						  	</el-form-item>
						  	<el-form-item label="Interest Rate" prop = "interest_rate">
						    	<el-input-number v-model="tdAccountDetails.interest_rate" controls-position="right" :disabled = "true"></el-input-number>
						  	</el-form-item>
						  	<a class = "click-class" @click="viewRateModal" >View rate list of the selected product here.</a>
						  	<span class = "pull-right"><el-button type = "primary" @click = "saveTDAccount" :disabled = "memberDetails.id == null">Save Time Deposit</el-button></span>
						</el-col>
						<el-col :span = "12">
							<h4>Member's Time Deposit List</h4>
							<el-table :data="accountTDList" style="width: 100%" stripe border>
					            <el-table-column label="Product">
					                <template slot-scope="scope">
					                    <span style="margin-left: 10px">{{ scope.row.product.description }}</span>
					                </template>
					            </el-table-column>
					            <el-table-column label="Amount">
					                <template slot-scope="scope">
					                    <span style="margin-left: 10px">{{ scope.row.amount }}</span>
					                </template>
					            </el-table-column>
					            <el-table-column label="Term">
					                <template slot-scope="scope">
					                    <span style="margin-left: 10px">{{ scope.row.term }}</span>
					                </template>
					            </el-table-column>
					            <el-table-column label="Maturity Date">
					                <template slot-scope="scope">
					                    <span style="margin-left: 10px">{{ scope.row.maturity_date }}</span>
					                </template>
					            </el-table-column>
		       				</el-table>
						</el-col>
					</el-row>
				</el-form>
			</div>
        </div>
        <search-member :base-url="baseUrl" v-if = "showSearchModal" @select="populateField" @close = "showSearchModal = false" >
	  	</search-member>
        <view-rate-list :product-detail="selectedProduct" v-if = "showRateList" @close = "showRateList = false" >
	  	</view-rate-list>
    </div>
</template>
<script>
	window.noty = require('noty');
    import axios from 'axios'
    import Noty from 'noty'
    import swal from 'sweetalert2/dist/sweetalert2.all.min.js'
    import cloneDeep from 'lodash/cloneDeep'

    import SearchMember from '../General/SearchMember.vue'  
    import ViewRateList from '../General/ViewRateList.vue'  

export default {
	props: ['dataTimeDepositAccount', 'dataTimeDepositTransaction', 'dataTimeDepositProduct', 'baseUrl'],
	data: function () {

    	let account  = {}
  		this.dataTimeDepositAccount.forEach(function(detail){
  			account[detail] = null
  		})

		return{
      		memberDetails			: {id : null, fullname : null},
      		showSearchModal			: false,
      		showRateList			: false,
      		tdAccountDetails 		: account,
      		accountTDList 			: [],
      		tdProductSelect 		: [],
      		tdProduct 				: this.dataTimeDepositProduct,
      		selectedProduct 		: null
		}
	},
	created(){
		let product = []
		this.tdProduct.forEach(function(detail){
  			let pr = {
  				label : detail.description,
  				value : detail.id,
  			}
  			product.push(pr)
  		})
		this.tdProductSelect = product

		let validateRate = (rule, value, callback) => {
			if ( value == null || value == '0' || value == 0) {
				callback(new Error('No available term rate for the selected product. Please view rate list.'));
			}
			else{
				callback();
			}
		}

		this.ruleAccount = {
  			fk_td_product : [{ required: true, message: 'Product cannot be blank.', trigger: 'change' },],
  			amount : [{ required: true, message: 'Amount type cannot be blank.', trigger: 'change' },],
  			term : [{ required: true, message: 'Term type cannot be blank.', trigger: 'change' },],
  			interest_rate : [{ validator: validateRate, trigger: 'blur' },],
		}

	},
    components: {
      	SearchMember,
      	ViewRateList
    },
    methods: {
    	viewRateModal(){
    		if(this.tdAccountDetails.fk_td_product){
    			this.showRateList = true
    		}
    		else{
    			new Noty({
                    theme: 'relax',
                    type: 'error',
                    layout: 'topRight',
                    text: 'Please select product to view rate list.',
                    timeout: 3000
                }).show();
    		}
    	},
    	populateField(data){
    		this.memberDetails = data
    		this.tdAccountDetails.member_id = data.id
    		this.getOtherAccounts(data.id)
    	},
    	productChange(val){
    		this.getRate(val, this.tdAccountDetails.term, this.tdAccountDetails.amount)
    	},
    	termChange(val){
    		this.getRate(this.tdAccountDetails.fk_td_product, val, this.tdAccountDetails.amount)
    	},
    	amountChange(val){
    		this.getRate(this.tdAccountDetails.fk_td_product, this.tdAccountDetails.term, val)
    	},
    	getRate(product_id, term_count, amount){
    		this.tdAccountDetails.interest_rate = null
    		if(product_id && term_count > 0 && amount > 0){
    			let productList = cloneDeep(this.tdProduct)
				let product = productList.find(ci => {
					return ci.id == Number(product_id)
				})
				if(product){
					let rate = product.ratetable.find(ci => {
						return Number(term_count) >= Number(ci.day_from) && Number(term_count) <=  Number(ci.day_to) && Number(amount) >= Number(ci.min_amount) && Number(amount) <= Number(ci.max_amount) 
					})
					if(rate){
						this.tdAccountDetails.interest_rate = rate.interest_rate
					}
				}
    		}
    	},
    	getOtherAccounts(member_id){

            let data = new FormData()
            data.set('member_id', member_id)

    		axios.post(this.baseUrl+'/time-deposit/get-td-accounts', data).then((result) => {
			    let res = result.data
                console.log(res)
                if(res.length > 0 ){
                    this.accountTDList = res
                    console.log("success")
                }
                else{
                    console.log("no result")
                } 
			}).catch(function (error) {
            
                console.log(error);

                if(error.response.status == 403)
                    location.reload()
            })
    		
    	},
    	saveTDAccount(){    	
    		let vm = this	
    		this.$refs.tdAccountDetails.validate((valid) => {
	          	if (valid) {
	          		swal({
	                  title: 'Save Time Deposit Account?',
	                  text: "Are you sure you want to save this transaction? This action cannot be undone.",
	                  showCancelButton: true,
	                  confirmButtonText: 'Proceed',
	                  confirmButtonColor: '#4087C5',
	                  focusConfirm: false,
	                  focusCancel: false,
	                  cancelButtonText: 'Cancel',
	                  reverseButtons: true,
	                  background: '#ff3366',
	                  width: '400px',
	                  padding: 0
		            }).then(function (result) {
					  	if (result.value) {
					    	let data = new FormData()
		            		data.set('accountDetails', JSON.stringify(vm.tdAccountDetails))
		            		axios.post(vm.baseUrl+'/time-deposit/save-td-account', data).then((result) => {
				                let res = result.data
				                let type = ""
				                let message = ""
				                console.log(res)
				                if(res.length > 0 ){
				                    console.log("success")
				                }
				                else{
				                    console.log("no result")
				                } 
				                //location.reload()
				                  
				            }).catch(function (error) {
				            
				                console.log(error);

				                if(error.response && error.response.status == 403)
				                    location.reload()
				            })
					  	} else {
					   		console.log("Cancel")
					  	}
					}) 
		            
	          	}
	          	else{
	          		return false
	          	}
	        })
    	},
    },
    watch: {
    	"tdAccountDetails.fk_td_product" : function (val) {
	      	if(val){
	      		let productList = cloneDeep(this.tdProduct)
				let product = productList.find(ci => {
					return ci.id == Number(val)
				})
	      		this.selectedProduct = product
	      	}
	    },
    }
}
</script>

<style lang="scss">
  	@import '../../assets/site.scss';
  	@import '~noty/src/noty.scss';

  	.time-deposit-form{

		.el-select{
			width: 100%;
		}

		.el-input-number{
			width: 100%;

			input{
				text-align: left;
			}
			
		}
	}
</style>