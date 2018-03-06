<template>
	<div class="member-form">
		<div class = "row">
			<div class = "col-md-6">
				<div class="form-group">
					<div class = "row">
				   		<label for="exampleInputPassword1" class = "col-md-3" >Name</label>
					    <div  class = "col-md-9 input-name">
						    <input type="text" class="form-control" v-model = "member.detail.first_name" style = "width: 33%;" placeholder = "First Name" required >
						    <input type="text" class="form-control" v-model = "member.detail.middle_name" style = "width: 28%;" placeholder = "Middle Name" required >
						    <input type="text" class="form-control" v-model = "member.detail.last_name" style = "width: 33%;" placeholder = "Last Name" required >
		                    <span v-if="errors.detail.first_name" class="help text-red">{{ errors.detail.first_name[0]}}</span>
		                    <span v-if="errors.detail.middle_name" class="help text-red">{{ errors.detail.middle_name[0] }}</span>
		                    <span v-if="errors.detail.last_name" class="help text-red">{{ errors.detail.last_name[0] }}</span>
						</div>
					</div>
				</div>
				
				<div class="form-group">
					<div class = "row">
					    <label for="exampleInputPassword1" class = "col-md-3">Username</label>
					    <div class = "col-md-9">
						    <input type="text" class="form-control"  v-model="member.user.username">
                    		<span v-if="errors.user.username" class="help text-red">{{ errors.user.username[0] }}</span>
						</div>
					</div>
				</div>
						
				<div class="form-group">
					<div class = "row">
					    <label for="exampleInputPassword1" class = "col-md-3">Password</label>
					    <div class = "col-md-9">
					    	<input type="password" class="form-control"  v-model="member.user.password">
                    		<span v-if="errors.user.password" class="help text-red">{{ errors.user.password[0] }}</span>
						</div>
					</div>
				</div>
						
				<div class="form-group">
					<div class = "row">
					    <label for="exampleInputPassword1" class = "col-md-3">Email</label>
					    <div class = "col-md-9">
					    	<input type="email" class="form-control"  v-model="member.user.email">
                    		<span v-if="errors.user.email" class="help text-red">{{ errors.user.email[0] }}</span>
						</div>
					</div>
				</div>

				<div class="form-group">
					<div class = "row">
						<label class = "col-md-3">Division</label>
						<div class = "col-md-9">
	                		<multiselect  v-model="member.detail.division_id" :options="divisionList" :searchable="false" :close-on-select="true" :allow-empty="false" label="label" placeholder="Select one" track-by="value" >
							</multiselect>
                    		<span v-if="errors.detail.division_id" class="help text-red">{{ errors.detail.division_id[0] }}</span>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class = "row">
					    <label class = "col-md-3">Station</label>
					    <div class = "col-md-9">
		              		<multiselect  v-model="member.detail.station_id" :options="stationList" :searchable="false" :close-on-select="true" :allow-empty="false" label="label" placeholder="Select one" track-by="value" >
							</multiselect>
                    		<span v-if="errors.detail.station_id" class="help text-red">{{ errors.detail.station_id[0] }}</span>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class = "row">
					    <label class = "col-md-3">Position</label>
					    <div class = "col-md-9">
					    	<input type="text" class="form-control" v-model = "member.detail.position">
					    </div>
					</div>
				</div>			
			</div>

			<div class = "col-md-6">
				<div class="form-group">
					<div class = "row">
						<label class = "col-md-3">Member Type</label>
						<div class = "col-md-9">
	                		<multiselect  v-model="member.detail.member_type_id" :options="typeList" :searchable="false" :close-on-select="true" :allow-empty="false" label="label" placeholder="Select one" track-by="value" >
							</multiselect>
                    		<span v-if="errors.detail.member_type_id" class="help text-red">{{ errors.detail.member_type_id[0] }}</span>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class = "row">
				    	<label class = "col-md-3">Membership Date</label>
					    <div class = "col-md-9">
				    		<date-picker v-model="member.detail.mem_date" type="date" format="yyyy-MM-dd" lang="en"></date-picker>
                    		<span v-if="errors.detail.mem_date" class="help text-red">{{ errors.detail.mem_date[0] }}</span>
				    	</div>
				    </div>
				</div>
				<div class="form-group">
					<div class = "row">
				    	<label class = "col-md-3">Birth date</label>
					    <div class = "col-md-9">
				    		<date-picker v-model="member.detail.birthday" type="date" format="yyyy-MM-dd" lang="en"></date-picker>
                    		<span v-if="errors.detail.birthday" class="help text-red">{{ errors.detail.birthday[0] }}</span>
				    	</div>
				    </div>
				</div>
				<div class="form-group">
					<div class = "row">
				    	<label class = "col-md-3">Gender</label>
					    <div class = "col-md-9">
				    		<multiselect  v-model="member.detail.gender" :options="genderList" :searchable="false" :close-on-select="true" :allow-empty="false" label="label" placeholder="Select one" track-by="value" >
							</multiselect>
                    		<span v-if="errors.detail.gender" class="help text-red">{{ errors.detail.gender[0] }}</span>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class = "row">
				    	<label class = "col-md-3">Civil Status</label>
					    <div class = "col-md-9">
				    		<multiselect  v-model="member.detail.civil_status" :options="statusList" :searchable="false" :close-on-select="true" :allow-empty="false" label="label" placeholder="Select one" track-by="value" >
							</multiselect>
                    		<span v-if="errors.detail.civil_status" class="help text-red">{{ errors.detail.civil_status[0] }}</span>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class = "row">
				    	<label class = "col-md-3">Salary</label>
					    <div class = "col-md-9">
				    		<input type="number" class="form-control" v-model="member.detail.salary">
				    	</div>
				    </div>
				</div>
				<div class="form-group">
					<div class = "row">
				    	<label class = "col-md-3">Tel. No</label>
					    <div class = "col-md-9">
				    		<input type="text" class="form-control" v-model = "member.detail.telephone">
				    	</div>
				    </div>
				</div>
			</div>
			<div class = "col-md-12">
				<hr>
			</div>
			<div class = "col-md-6">								
				<label>Primary Address:</label>
				<div class="form-group">
					<div class = "row">
				    	<label class = "col-md-3">Address</label>
					    <div class = "col-md-9">
						    <input type="text" class="form-control" v-model = "member.address.address" >
                    		<span v-if="errors.address.address" class="help text-red">{{ errors.address.address[0] }}</span>
				    	</div>
				    </div>
				</div>	
				<div class="form-group">
					<div class = "row">
				    	<label class = "col-md-3">City</label>
					    <div class = "col-md-9">
						    <input type="text" class="form-control" v-model = "member.address.city" >
				    	</div>
				    </div>
				</div>	
				<div class="form-group">
					<div class = "row">
				    	<label class = "col-md-3">Province</label>
					    <div class = "col-md-9">
						    <input type="text" class="form-control" v-model = "member.address.province" >
				    	</div>
				    </div>
				</div>
			</div>
			<div class = "col-md-6">
				<label>In case of Emergency:</label>
				<div class = "form-group">
					<div class = "row">
				    	<label class = "col-md-3">Name</label>
					    <div  class = "col-md-9">
						    <input type="text" class="form-control" v-model = "member.family.name" >
                    		<span v-if="errors.family.name" class="help text-red">{{ errors.family.name[0] }}</span>
						</div>
				    </div>
				</div>
				<div class = "form-group">
					<div class = "row">
				    	<label class = "col-md-3">Relation</label>
					    <div  class = "col-md-9">
						    <input type="text" class="form-control" v-model = "member.family.relation" >
                    		<span v-if="errors.family.relation" class="help text-red">{{ errors.family.relation[0] }}</span>
						</div>
				    </div>
				</div>
				<div class = "form-group">
					<div class = "row">
				    	<label class = "col-md-3">Address</label>
					    <div  class = "col-md-9">
						    <textarea v-model = "member.family.address" class="form-control"></textarea>
						</div>
				    </div>
				</div>
				<div class = "form-group">
					<div class = "row">
				    	<label class = "col-md-3">Contact No.</label>
					    <div  class = "col-md-9">
						    <input type="text" class="form-control" v-model = "member.family.contact_no" >
						</div>
				    </div>
				</div>
			</div>
			<div class = "col-md-12">
				<button class = "btn btn-primary" @click = "saveForm()">Save</button>
			</div>
		</div>
	</div>
</template>

<style lang="scss">
  	@import '../../assets/member.scss';
  	@import '~noty/src/noty.scss'
</style>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
<style src="vue-tabs-component/docs/resources/tabs-component.css"></style>

<script>

	window.noty = require('noty')

	import axios from 'axios'
    import Noty from 'noty'
    import Multiselect from 'vue-multiselect'
    import DatePicker from 'vue2-datepicker'
    import VueTimepicker from 'vue2-timepicker'
    import {Tabs, Tab} from 'vue-tabs-component'
    import VueTabs from 'vue-nav-tabs'
    import VTab from 'vue-nav-tabs'

export default {
	props: ['dataMember', 'dataUser', 'dataFamily', 'dataAddress', 'dataStationList', 'dataDivisionList', 'dataTypeList', 'dataBranchList', 'baseUrl'],
	data: function () {

		let gender = [{ value : "Male", label : "Male"}, { value : "Female", label : "Female"}]
		let status = [{ value : "Single", label : "Single"}, 
					{ value : "Married", label : "Married"}, 
					{ value : "Widow", label : "Widow"}, 
					{ value : "Separated", label : "Separated"}]
		let tabId = ['general_info', 'member_details', 'member_details_2']

		return {
			member 			: {detail : this.dataMember, user : this.dataUser, family : this.dataFamily, address : this.dataAddress},
			stationList 	: this.dataStationList,
			divisionList 	: this.dataDivisionList,
			typeList 		: this.dataTypeList,
			branchList 		: this.dataBranchList,
			birthdateVal	: moment().format('YYYY-MM-DD'),
			genderVal		: gender[0],
			genderList		: gender,
			statusVal		: status[0],
			statusList		: status,
			currentTab		: 0,
			tabList 		: tabId,
			errors 			: {detail : {}, user : {}, family : {}, address : {}},
            dateconfig		: {
              	format 		: 'MMM D YYYY',
              	useCurrent	: false,
              	showClear	: true,
              	showClose	: true,
            },
		}
	},
    components: {
        DatePicker,
        VueTimepicker,
        Multiselect,
        Tabs,
        Tab
    }, 
    mounted: function () {
    	
    },
    methods: {    	
        formatDate(date, format){
          	if(date){
            	return moment(date).format(format)
          	}
          	return null
        },
    	saveForm(){

	    	let main_preloader = document.getElementById('main_preloader');
	        main_preloader.style.display = 'block';

    		let vm = this

    		vm.errors.detail = {}
    		vm.errors.user = {}
    		vm.errors.family = {}
    		vm.errors.address = {}

    		let new_member = this.member

    		if(vm.member.birthday != null){
    			new_member.detail.birthday = vm.formatDate(vm.member.detail.birthday, "YYYY-MM-DD")
    		}
    		new_member.detail.mem_date = vm.formatDate(vm.member.detail.mem_date, "YYYY-MM-DD")
    		console.log(new_member)


    		let data = new FormData()

    		data.set('employee', JSON.stringify(new_member))

            axios.post(this.baseUrl+'/member/save-member', data).then((result) => {
            	let res = result.data
            	let type = ""
            	let message = ""
            	console.log(res)
            	if(res.success == true){
            		type = "success"
            		message = "Member successfully added."
            	}
            	else{
            		if(res.status == 'has-error'){
	            		vm.errors.detail = res.error.detail
	            		vm.errors.user = res.error.user
	            		vm.errors.family = res.error.family
	            		vm.errors.address = res.error.address
	            		type = "warning"
            			message = "Please fill all field correctly."
            		}
            		else{
	            		type = "error"
            			message = "Member not save. Please try again or contact administrator."
            		}
            	}

            	new Noty({
	                theme: 'relax',
	                type: 'success',
	                layout: 'topRight',
	                text: message,
	                timeout: 2500
	            }).show();

	            main_preloader.style.display = 'none';

            }).catch(function (error) {
 				main_preloader.style.display = 'none';
    			new Noty({
		            theme: 'relax',
		            type: 'error',
		            layout: 'topRight',
		            text: 'An error occured. Please try again or contact administrator',
		            timeout: 2500
		        }).show()

    			if(error.response.status == 403)
    				location.reload()
  			})

    	}

    }
}

</script>