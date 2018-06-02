<template>
	<div class = "member-family">
		<span class = "pull-right" style = "margin-bottom: 10px;"><button class = "btn btn-sm btn-primary" @click = "$refs.addFamilyModal.open(); dialogVisible = true;">Add</button></span>
		<table class = "table table-bordered table-hover dataTable">
			<thead>
				<tr>
					<th>Name</th>
					<th>Relation</th>
					<th>Address</th>
					<th>Contact</th>
					<th width = "20px" v-if = "canEdit">Action</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="(family, index) in memberFamilyList" :key = "index">
					<td>
						<form-info ref = "name" :value = "family.name" props = "name" label = "Name" @update = "updateInfo" :other-details = "{id : family.id, index : index}" v-if = "canEdit"></form-info>
						<span v-else >{{ family.name }}</span>
					</td>
					<td>
						<form-info ref = "relation" :value = "family.relation" props = "relation" label = "Relation" @update = "updateInfo" :other-details = "{id : family.id, index : index}" v-if = "canEdit"></form-info>
						<span v-else >{{ family.relation }}</span>
					</td>
					<td>
						<form-info ref = "fam_address" :value = "family.fam_address" props = "fam_address" label = "Address" @update = "updateInfo" :other-details = "{id : family.id, index : index}" v-if = "canEdit"></form-info>
						<span v-else >{{ family.fam_address }}</span>
					</td>
					<td>
						<form-info ref = "contact_no" :value = "family.contact_no" props = "contact_no" label = "Contact" @update = "updateInfo" :other-details = "{id : family.id, index : index}" v-if = "canEdit"></form-info>
						<span v-else >{{ family.contact_no }}</span>
					</td>
					<td v-if = "canEdit">
						<span class = "text-red pointer"  @click = "deleteInfo(family.id)"><i class="fa fa-fw fa-trash"></i></span>
					</td>
				</tr>
			</tbody>
		</table>

		<el-dialog  ref="addFamilyModal" class = "addModal" title = "Add Family" :visible.sync="dialogVisible">
			<div class = "row">
				<div class = "col-md-12">
					<el-form :model="familyModel" ref="familyModel" :rules="rules" position = "top" @submit.native.prevent>
						<el-form-item label="Name" prop="name" ref = "name">
					    	<el-input type = "text" v-model="familyModel.name" placeholder="Press enter to name"></el-input>
					    </el-form-item>

						<el-form-item label="Relation" prop="relation" ref = "relation">
					    	<el-input type = "text" v-model="familyModel.relation" placeholder="Press enter to relation"></el-input>
					    </el-form-item>

						<el-form-item label="Address" prop="fam_address" ref = "fam_address">
					    	<el-input type = "text" v-model="familyModel.fam_address" placeholder="Press enter to address"></el-input>
					    </el-form-item>

						<el-form-item label="Contact" prop="contact_no" ref = "contact_no">
					    	<el-input type = "text" v-model="familyModel.contact_no" placeholder="Press enter to contact"></el-input>
					    </el-form-item>
					</el-form>
				    <div>
				    	<button class="btn btn-sm btn-primary submit" @click="addMemberFamily('familyModel')">Save</button>
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
import Swal from 'sweetalert2'
import FormInfo from '../FormInfo'

export default {
	props: ['memberFamily', 'memberId', 'canEdit'],
	data: function () {
		return{
			familyModel			: {name : null, relation : null, fam_address : null, contact_no : null},
			memberFamilyList 	: this.memberFamily,
			dialogVisible		: false,
			rules 				: null
		}
	},
	created(){
		this.rules = {
			name : [ { required: true, message: 'Name cannot be blank.', trigger: 'blur' },
			]
		}
	},
	components: {
		FormInfo
	},
	methods:{		
    	addMemberFamily(form){
    		let vm = this
	        this.$refs[form].validate((valid) => {
	          	if (valid) {

		    		let data = new FormData()

		    		data.set('familyMember', JSON.stringify(vm.familyModel))
		    		data.set('member_id', this.memberId)

	            	axios.post(vm.$parent.baseUrl+'/member/add-member-family', data).then((result) => {
	            		let res = result.data
	            		let type = ""
	            		let message = ""
	            		if(res.success == true){
		            		type = "success"
		            		message = "Member family successfully added."
	            			vm.memberFamilyList.push(res.data)
	            			new Noty({
				                theme: 'relax',
				                type: type ,
				                layout: 'topRight',
				                text: message,
				                timeout: 2500
				            }).show();

				            vm.$refs[form].resetFields();

				            vm.$refs['addFamilyModal'].close()
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
    		let family_id = emitData['details']['id']

    		let index = vm.memberFamilyList.findIndex(ci => {return ci.id == Number(family_id)})

    		let label = emitData['label']

    		data.set('family_id', family_id)
    		data.set('label', label)
    		data.set('value', emitData['value'])

    		axios.post(this.$parent.baseUrl+'/member/update-member-family', data).then((result) => {
	    		let res = result.data
	    		let type = ""
	    		let message = ""
	    		if(res.success == true){
	        		type = "success"
	        		message = "Member family successfully update."
	        		
	        		vm.memberFamilyList.splice(index, 1, res.data)

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
		        		message = "Family not updated. Please try again or contact administrator."

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
    	deleteInfo(family_id){
    		let vm = this
    		Swal({
                title: 'Delete Family?',
                text: "Are you sure you want to delete selected family? This action cannot be undone.",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: 'Proceed',
                confirmButtonColor: '#d33',
  				confirmButtonClass: 'btn btn-danger',
                focusConfirm: false,
                focusCancel: false,
                cancelButtonText: 'Cancel',
  				cancelButtonClass: 'btn',
                reverseButtons: true,
                background: '#fff',
                width: '400px',
                padding: 0
            }).then(function() {

	    		let data = new FormData()
	    		data.set('family_id', family_id)

	    		axios.post(vm.$parent.baseUrl+'/member/delete-member-family', data).then((result) => {
		    		let res = result.data
		    		let type = ""
		    		let message = ""
		    		if(res.success == true){
		        		type = "success"
		        		message = "Member family successfully deleted."
                		let index = vm.memberFamilyList.findIndex(ci => {return ci.id == Number(res.data)})
		        		
		        		vm.memberFamilyList.splice(index, 1)

		    		}
		        	else{
		        		type = "error"
			        	message = "Family not deleted. Please try again or contact administrator."
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
            }, function(dismiss) {

            }) 

    		
    	}
	}
}
</script>