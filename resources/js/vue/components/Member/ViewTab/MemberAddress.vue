<template>
	<div class = "member-address">
		<span class = "pull-right" style = "margin-bottom: 10px;"><button class = "btn btn-sm btn-primary" @click = "$refs.addAddressModal.open(); dialogVisible = true;" v-if = "canEdit">Add</button></span>
		<table class = "table table-bordered table-hover dataTable">
			<thead>
				<tr>
					<th>Address</th>
					<th>City</th>
					<th>Province</th>
					<th width = "20px" v-if = "canEdit">Action</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="(address, index) in memberAddressList" :key = "index">
					<td>
						<form-info ref = "con_address" :value = "address.con_address" props = "con_address" label = "Address" @update = "updateInfo" :other-details = "{id : address.id, index : index}" v-if = "canEdit"></form-info>
							<span v-else >{{ address.con_address }}</span>
					</td>
					<td>
						<form-info ref = "city" :value = "address.city" props = "city" label = "City" @update = "updateInfo" :other-details = "{id : address.id, index : index}" v-if = "canEdit"></form-info>
							<span v-else >{{ address.city }}</span>
					</td>
					<td>
						<form-info ref = "province" :value = "address.province" props = "province" label = "Province" @update = "updateInfo" :other-details = "{id : address.id, index : index}" v-if = "canEdit"></form-info>
							<span v-else >{{ address.province }}</span>
					</td>
					<td v-if = "canEdit">
						<span class = "text-red pointer"  @click = "deleteInfo(address.id)"><i class="fa fa-fw fa-trash"></i></span>
					</td>
				</tr>
			</tbody>
		</table>

		<el-dialog  ref="addAddressModal" class = "addModal" title = "Add Address" :visible.sync="dialogVisible">
			<div class = "row">
				<div class = "col-md-12">
					<el-form :model="addressModel" ref="addressModel" :rules="rules" position = "top" @submit.native.prevent>
						<el-form-item label="Address" prop="con_address" ref = "add_con_address">
					    	<el-input type = "text" v-model="addressModel.con_address" placeholder="Press enter to address"></el-input>
					    </el-form-item>

						<el-form-item label="City" prop="city" ref = "add_city">
					    	<el-input type = "text" v-model="addressModel.city" placeholder="Press enter to city"></el-input>
					    </el-form-item>

						<el-form-item label="Province" prop="province" ref = "add_province">
					    	<el-input type = "text" v-model="addressModel.province" placeholder="Press enter to province"></el-input>
					    </el-form-item>
					</el-form>
				    <div>
				    	<button class="btn btn-sm btn-primary submit" @click="addMemberAddress('addressModel')">Save</button>
				    </div>
				</div>
			</div>
		</el-dialog>
	</div>
</template>
<script>
window.noty = require('noty')

import axios from 'axios'
import Noty from 'noty'
import FormInfo from '../FormInfo'

export default {
	props: ['memberAddress', 'memberId', 'canEdit'],
	data: function () {
		return{
			addressModel		: {con_address : null, city : null, province : null},
			memberAddressList 	: this.memberAddress,
			dialogVisible		: false,
			rules 				: null
		}
	},
	created(){
		this.rules = {
			con_address : [ { required: true, message: 'Address cannot be blank.', trigger: 'blur' },
			]
		}
	},
	components: {
		FormInfo
	},
	methods:{		
    	addMemberAddress(form){
    		let vm = this
	        this.$refs[form].validate((valid) => {
	          	if (valid) {

		    		let data = new FormData()

		    		data.set('addressMember', JSON.stringify(vm.addressModel))
		    		data.set('member_id', this.memberId)

	            	axios.post(vm.$parent.baseUrl+'/member/add-member-address', data).then((result) => {
	            		let res = result.data
	            		let type = ""
	            		let message = ""
	            		if(res.success == true){
		            		type = "success"
		            		message = "Member address successfully added."
	            			vm.memberAddressList.push(res.data)
	            			new Noty({
				                theme: 'relax',
				                type: type ,
				                layout: 'topRight',
				                text: message,
				                timeout: 2500
				            }).show();

				            vm.$refs[form].resetFields();

				            vm.$refs['addAddressModal'].close()
				            vm.dialogVisible = false
	            		}
		            	else{
		            		if(res.status == "has-error"){
		            			Object.keys(res.data).forEach(function(key) {
		            				vm.$refs['add_' + key].validateMessage = res.data[key][0]
		            				vm.$refs['add_' + key].validateState = error
								});
		            		}
		            	}            	

	            	}).catch(function (error) {
	            		console.log(error)
		    			new Noty({
				            theme: 'relax',
				            type: 'error',
				            layout: 'topRight',
				            text: 'An error occured. Please try again or contact administrator',
				            timeout: 2500
				        }).show()

		    			if(error.response && error.response.status == 403)
		    				location.reload()
		  			})
	          	} else {
	            	console.log('error submit!!');
	            	return false;
	          	}
	        });

    	},
    	updateInfo(emitData){
    		let vm = this

    		let data = new FormData()
    		let address_id = emitData['details']['id']
    		console.log(address_id)
    		let index = vm.memberAddressList.findIndex(ci => {return ci.id == Number(address_id)})
    		console.log(index)
    		let label = emitData['label']

    		data.set('address_id', address_id)
    		data.set('label', label)
    		data.set('value', emitData['value'])

    		axios.post(this.$parent.baseUrl+'/member/update-member-address', data).then((result) => {
	    		let res = result.data
	    		let type = ""
	    		let message = ""
	    		if(res.success == true){
	        		type = "success"
	        		message = "Member address successfully update."
	        		
	        		vm.memberAddressList.splice(index, 1, res.data)

	    			new Noty({
		                theme: 'relax',
		                type: type ,
		                layout: 'topRight',
		                text: message,
		                timeout: 2500
		            }).show();
	    		}
	        	else{
	        		if(res.status == "has-error"){

        				vm.$refs[label][index].validateMessage = res.data[label][0]
	        			vm.$refs[label][index].validateState = error
	        		}
	        		else{
	        			type = "error"
		        		message = "Address not updated. Please try again or contact administrator."

		    			new Noty({
			                theme: 'relax',
			                type: type ,
			                layout: 'topRight',
			                text: message,
			                timeout: 2500
			            }).show();
	        		}
	        	}            	

	    	}).catch(function (error) {
				new Noty({
		            theme: 'relax',
		            type: 'error',
		            layout: 'topRight',
		            text: 'An error occured. Please try again or contact administrator',
		            timeout: 2500
		        }).show()

				if(error.response && error.response.status == 403)
					location.reload()
			})
    	},
    	deleteInfo(address_id){
    		let vm = this
    		vm.$swal({
              	title: 'Delete Address?',
              	text: "Are you sure you want to delete selected address? This action cannot be undone.",
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
		    		let data = new FormData()
		    		data.set('address_id', address_id)

		    		axios.post(vm.$parent.baseUrl+'/member/delete-member-address', data).then((result) => {
			    		let res = result.data
			    		let type = ""
			    		let message = ""
			    		if(res.success == true){
			        		type = "success"
			        		message = "Member address successfully deleted."
	                		let index = vm.memberAddressList.findIndex(ci => {return ci.id == Number(res.data)})
			        		
			        		vm.memberAddressList.splice(index, 1)

			    		}
			        	else{
			        		type = "error"
				        	message = "Address not deleted. Please try again or contact administrator."
			        	} 


		    			new Noty({
			                theme: 'relax',
			                type: type ,
			                layout: 'topRight',
			                text: message,
			                timeout: 2500
			            }).show();           	

			    	}).catch(function (error) {
						new Noty({
				            theme: 'relax',
				            type: 'error',
				            layout: 'topRight',
				            text: 'An error occured. Please try again or contact administrator',
				            timeout: 2500
				        }).show()

						if(error.response && error.response.status == 403)
							location.reload()
					})
			    }
            }) 

    		
    	}
	}
}
</script>