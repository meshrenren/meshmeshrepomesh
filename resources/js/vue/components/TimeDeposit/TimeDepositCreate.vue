<template>
	<div class="time-deposit-form">
		<div class="box box-info">
            <div class="box-header with-border">
              	<h3 class="box-title">Time Deposit Account</h3>
            </div>
	        <div class = "box-body">
	        	<el-row :gutter = "20">			
					<el-col :span="12">
						<el-input v-model="accountFilter" autofocus placeholder = "Search Account">
		                </el-input>
						<el-table :data="accountListData"  height="450" stripe border style = "margin-top:10px;">
				            <el-table-column label="Account Number">
				                <template slot-scope="scope">
				                    <span style="margin-left: 10px">{{ scope.row.accountnumber }}</span>
				                </template>
				            </el-table-column>
				            <el-table-column label="Account Name">
				                <template slot-scope="scope">
				                    <span  style="margin-left: 10px">{{ scope.row.account_name }}</span>
				                </template>
				            </el-table-column>
				            <el-table-column label="Amount">
				                <template slot-scope="scope">
				                    <span style="margin-left: 10px">{{ $nf.formatNumber(scope.row.amount, 2) }}</span>
				                </template>
				            </el-table-column>
				            <el-table-column label="Maturity Date">
				                <template slot-scope="scope">
				                    <span style="margin-left: 10px">{{ $df.formatDate(scope.row.maturity_date, "MMM DD, YYYY") }}</span>
				                </template>
				            </el-table-column>
				        </el-table>
					</el-col>
        			<el-col :span="12">
						<el-form label-position="right" label-width="180px" :model="tdAccountDetails" :rules = "ruleAccount" ref = "tdAccountDetails">
	        				<el-form-item label="Type" prop = "type">
								<el-select v-model="tdAccountDetails.type" placeholder="Select type" @change = "selectType">
								    <el-option abel="Member" value="Member"></el-option>
								    <el-option abel="Group" value="Group"></el-option>
								</el-select>
						  	</el-form-item>
						  	<el-form-item label="Name" prop = "account_name">
				                <el-input v-model="tdAccountDetails.account_name" :disabled = "tdAccountDetails.type == 'Member' ? true:false">
				                    <el-button slot="append" type = "primary" @click="getMember()" v-if = "tdAccountDetails.type == 'Member'">Find Member</el-button>
				                </el-input>
				            </el-form-item>
						  	<el-form-item label="Product" prop = "fk_td_product">
						    	<el-select v-model="tdAccountDetails.fk_td_product" placeholder="Select" @change = "productChange" ref = "fk_td_product" :disabled = "true">
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
						  	<el-form-item label="Term (Months)" prop = "term">
						  		<el-select v-model="tdAccountDetails.term" placeholder="Select" @change = "termChange" ref = "term" >
								    <el-option
								      v-for="(item, ind) in termList"
								      :key="ind"
								      :label="item"
								      :value="item">
								    </el-option>
								</el-select>
						  	</el-form-item>

						  	<el-form-item label="Date)" prop = "open_date">
						  		<el-date-picker v-model="tdAccountDetails.open_date" type="date" placeholder="Pick a date"> </el-date-picker>
						  	</el-form-item>
						  	<el-form-item label="Interest Rate" prop = "interest_rate">
						    	<el-input v-model="tdAccountDetails.interest_rate" >
						    		<span slot="append">%</span>
						    	</el-input>
						    	<!-- <el-input-number v-model="tdAccountDetails.interest_rate" controls-position="right" :disabled = "true">
						    		<span slot="append">%</span>
						    	</el-input-number> -->
						  	</el-form-item>
						  	<el-form-item label="Service Charge" prop = "service_amount">
						    	<el-input-number v-model="tdAccountDetails.service_amount" :controls = "false">
						    	</el-input-number>
						    	<!-- <el-input-number v-model="tdAccountDetails.interest_rate" controls-position="right" :disabled = "true">
						    		<span slot="append">%</span>
						    	</el-input-number> -->
						  	</el-form-item>
						  	<el-form-item label="Or Number (Ref. No)" prop = "or_number">
						    	<el-input v-model="tdAccountDetails.or_number" :controls = "false">
						    	</el-input>
						  	</el-form-item>
							  Service Fee: {{ $nf.formatNumber(serviceFee, 2) }} <br/>
							  Total Amount to be Incurred: {{ $nf.formatNumber(totalIncurred, 2) }} <br/>
						  	<a class = "click-class" @click="viewRateModal" >View rate list of the selected product here. </a>

						  	<div class = "signatory" v-if = "tdAccountDetails.type == 'Group'">
								<el-button type = "info" @click="getSignatory()">Add Signatory</el-button>
								<el-table :data="signatoryList" style="width: auto; margin-top:10px;" stripe border>
						            <el-table-column label="Member Signatory">
						                <template slot-scope="scope">
						                    <span style="margin-left: 10px">{{ scope.row.fullname }}</span>
						                </template>
						            </el-table-column>
						            <el-table-column label="Action">
						                <template slot-scope="scope">
						                    <el-button type = "danger" @click="removeSignatory(scope.row)">Remove</el-button>
						                </template>
						            </el-table-column>
						        </el-table>
							</div>

						 	<div style="margin-top: 10px;">
								<span class = "pull-right">
									<!-- <el-button @click="cancelForm">Cancel</el-button> -->
									<el-button type = "primary" @click = "saveTDAccount">Save Time Deposit</el-button>
								</span>
							</div>
						</el-form>
					</el-col>
				</el-row>
			</div>
        </div>
        <search-member :base-url="baseUrl" :show-modal = "showSearchModal" @select="populateField" @close = "showSearchModal = false" >
	  		</search-member>
        <view-rate-list :product-detail="selectedProduct" v-if = "showRateList" @close = "showRateList = false" >
	  	</view-rate-list>
    </div>
</template>
<script>
	window.noty = require('noty');
    import axios from 'axios'
    import Noty from 'noty'
    import cloneDeep from 'lodash/cloneDeep'

    import SearchMember from '../General/SearchMember.vue'  
    import ViewRateList from '../General/ViewRateList.vue'  
    import _forEach from 'lodash/forEach'

export default {
	props: ['dataTimeDepositAccount', 'dataTimeDepositTransaction', 'dataTimeDepositProduct', 'dataTimeDepositList', 'baseUrl', 'dataTdRates'],
	data: function () {

    	let account  = {}
  		this.dataTimeDepositAccount.forEach(function(detail){
  			account[detail] = null
  		})
  		account['or_number'] = null

		return{
      		memberDetails			: {id : null, fullname : null},
      		showSearchModal			: false,
      		showRateList			: false,
      		tdAccountDetails 		: account,
      		accountTDList 			: [],
      		tdProductSelect 		: [],
      		tdProduct 				: this.dataTimeDepositProduct,
      		selectedProduct 		: null,
      		accountList 			: this.dataTimeDepositList,
      		accountFilter 			: "",
      		isGet 					: "",
			signatoryList			: [],
			serviceFee: 0,
			totalIncurred: 0
		}
	},
	created(){
		//this.showSearchModal = true
		let product = []
		let defaultProduct = null
		this.tdProduct.forEach(function(detail){
  			let pr = {
  				label : detail.description,
  				value : detail.id,
  			}
  			product.push(pr)
  			defaultProduct = detail.id
  		})
		this.tdProductSelect = product

		this.tdAccountDetails.fk_td_product = defaultProduct
		this.tdAccountDetails.type = "Member"


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
  			account_name : [{ required: true, message: 'Name cannot be blank.', trigger: 'change' },],
  			amount : [{ required: true, message: 'Amount type cannot be blank.', trigger: 'change' },],
  			term : [{ required: true, message: 'Term type cannot be blank.', trigger: 'change' },],
  			or_number : [{ required: true, message: 'OR Number cannot be blank.', trigger: 'change' },],
  			interest_rate : [{ validator: validateRate, trigger: 'blur' },],
		}

	},
    components: {
      	SearchMember,
      	ViewRateList
    },
    computed:{
    	accountListData(){
    		let datalist = this.accountList
            let filterKey = this.accountFilter

            _forEach(datalist, function(element, index) {
            	if(element.type == "Member" && element.member){
            		element.account_name = element.member.fullname
            	}
            	
            })

            if (filterKey) {
                if(datalist){
                    datalist = datalist.filter(function (row) {
                    	return  String(row.account_name).toLowerCase().indexOf(filterKey) > -1
                    })
                }
            }

            return datalist
    	},
    	termList(){
    		let product = this.tdProduct.find(ci => { return Number(ci.id) == this.tdAccountDetails.fk_td_product})
    		let ratelist = cloneDeep(product.ratetable)
    		let list = []

    		_forEach(ratelist, function(element, index) {
    			let getInd = list.findIndex(ci => { return Number(ci) == Number(element.days)})
            	if(getInd < 0){
            		list.push(element.days)
            	}
            	
            })

            return list
    	}
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
    	selectType(val){
    		if(val == "Member"){
    			if(!this.tdAccountDetails.account_name){
    				this.isGet = "AccountMember"
    				this.showSearchModal = true
    			}
    		}
    		else if(val == "Group"){
    			this.tdAccountDetails.account_name = ""
    			this.tdAccountDetails.member_id = null
    		}
    	},  
    	getSignatory(){
    		this.isGet = "AccountSignatory"
    		this.showSearchModal = true
    	},
    	removeSignatory(data){
    		let index = this.signatoryList.findIndex(ci => {return Number(ci.id) == Number(data.id)})		
			if (index >= 0){ 
				this.signatoryList.splice(index, 1)
			}
    	},  	
    	getMember(){
    		this.isGet = "AccountMember"
    		this.showSearchModal = true
    	},
    	/*populateField(data){
    		this.memberDetails = data
    		this.tdAccountDetails.member_id = data.id
    		this.getOtherAccounts(data.id)


    		this.$refs.fk_td_product.focus()
    	},*/
    	populateField(data){
    		if(this.isGet == "AccountMember"){
    			console.log("data", data)
    			this.tdAccountDetails.account_name = data.fullname
    			this.tdAccountDetails.member_id = data.id
    		}
    		else if(this.isGet == "AccountSignatory"){
    			let getInd = this.signatoryList.findIndex(mem => Number(mem.id) == Number(data.id))
    			if(getInd < 0){
    				this.signatoryList.push(data)
    			}
    		}
    		this.memberDetails = data
    		
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
						return Number(term_count) == Number(ci.days) && Number(amount) >= Number(ci.min_amount) && Number(amount) <= Number(ci.max_amount) 
					})
					if(rate){
						this.tdAccountDetails.interest_rate = rate.interest_rate
						//Associate or Extended
						this.serviceFee = 0
						if(this.memberDetails.member_type_id){
							let interestAmount = Number(this.tdAccountDetails.amount) * (Number(rate.interest_rate)/100)
							let serviceCharge =  this.$nf.numberFixed(interestAmount, 2) * (Number(rate.interest_rate)/100)
							this.serviceFee = this.$nf.numberFixed(serviceCharge, 2)
						}
						this.totalIncurred = Number(this.serviceFee) + this.tdAccountDetails.amount
						this.tdAccountDetails.service_amount = this.serviceFee
						
					}
				}
    		}
    	},
    	getOtherAccounts(member_id){

            let data = new FormData()
            data.set('member_id', member_id)

    		axios.post(this.$baseUrl+'/time-deposit/get-td-accounts', data).then((result) => {
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
    		if(this.tdAccountDetails.type == "Group" && this.signatoryList.length == 0)
    		{
    			new Noty({
		            theme: 'relax',
		            type: "error",
		            layout: 'topRight',
		            text: 'Please add signatory member for group Time Deposit account.',
		            timeout: 2500
		        }).show()

		        return false
    		}

    		this.$refs.tdAccountDetails.validate((valid) => {
	          	if (valid) {
	          		vm.$swal({
		              	title: 'Save Time Deposit Account?',
		              	text: "Are you sure you want to save this transaction? This action cannot be undone.",
		              	type: 'warning',
		              	showCancelButton: true,
		              	cancelButtonColor: '#d33',
		              	confirmButtonText: 'Proceed',
		              	focusConfirm: false,
		              	focusCancel: true,
		              	cancelButtonText: 'Cancel',
		              	reverseButtons: true,
		              	width: '400px',
		            }).then(function(result) {
		            	if (result.value) {
		            		let tdAcc = cloneDeep(vm.tdAccountDetails)
		            		tdAcc.open_date = vm.$df.formatDate(tdAcc.open_date, "YYYY-MM-DD")
		            		let data = {
			            		accountDetails : tdAcc,
			            		signatoryList : vm.signatoryList
			            	}

					    	/*let data = new FormData()
		            		data.set('accountDetails', JSON.stringify(vm.tdAccountDetails))*/
		            		axios.post(vm.$baseUrl+'/time-deposit/save-td-account', data).then((result) => {
				                let res = result.data
				                let type = ""
				                let message = ""
				                console.log(res)
				                if(res.success){
				                	type = "success"
				                	message = "Time Deposit Account successfully created."

				                	new Noty({
							            theme: 'relax',
							            type: type,
							            layout: 'topRight',
							            text: message,
							            timeout: 2500
							        }).show()
				                	location.reload()
				                }
				                else{
				                	type = "error"
				                	message = "Savings account not successfully created. Please try again or contact administrator."
				                    console.log("no result")
				                } 
				                new Noty({
						            theme: 'relax',
						            type: type,
						            layout: 'topRight',
						            text: message,
						            timeout: 2500
						        }).show()
				              //  location.reload()
				                  
				            }).catch(function (error) {
				            
				                console.log(error);

				                if(error.response && error.response.status == 403) {}
				                 //   location.reload()
				            })
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