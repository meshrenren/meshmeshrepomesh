<template>
	<div class="member-form">
		<section class="content-header">
	      	<h1 v-html = "getViewTitle()">
	      	</h1>
	      	<ol class="breadcrumb">
		        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
		        <li v-if = "pageView == 'member-create'"><a @click = "pageView = 'member-list'">Employee List</a></li>
		        <li v-else  class="active" v-html = "getViewTitle()"></li>
		        <li v-if = "pageView == 'member-create'" class="active" v-html = "getViewTitle()"></li>
	      	</ol>
	    </section>

		<div class = "box">
			<div class = "box-header" v-if = "pageView == 'member-create'">
				<h3 class="box-title"></h3>
				<div class="box-tools pull-right">
            		<button class = "btn btn-primary" title="Save Member" @click = "saveForm()">Save</button>
            		<button class="btn btn-default" title="Back to List" @click = "pageView = 'member-list'">Back to List</button>
            	</div>
			</div>
	        <div class = "box-body">
				<div class = "row member-list" v-if = "pageView == 'member-list'">
					<div class = "toolbar">
						<button type="button" class="btn btn-xs btn-default" @click = "pageView = 'member-create'">Create Member</button>
					</div>
					<datatable ref="tickettable" title="" :perPage="[10, 25, 50, 100]" :exportable="true" :printable="true" :columns="columnList" :rows="listMember" :sortable="false" :searchable="true" :exactSearch="false" >
					</datatable>
				</div>
				<div class = "row member-create" v-if = "pageView == 'member-create'">
					<div class = "col-md-6">
						<div class="form-group">
							<div class = "row">
						   		<label for="exampleInputPassword1" class = "col-md-3" >Name</label>
							    <div  class = "col-md-9 input-name">
								    <input type="text" class="form-control" v-model = "memberForm.detail.first_name" style = "width: 33%;" placeholder = "First Name" required >
								    <input type="text" class="form-control" v-model = "memberForm.detail.middle_name" style = "width: 28%;" placeholder = "Middle Name" required >
								    <input type="text" class="form-control" v-model = "memberForm.detail.last_name" style = "width: 33%;" placeholder = "Last Name" required >
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
								    <input type="text" class="form-control"  v-model="memberForm.user.username">
		                    		<span v-if="errors.user.username" class="help text-red">{{ errors.user.username[0] }}</span>
								</div>
							</div>
						</div>
								
						<div class="form-group">
							<div class = "row">
							    <label for="exampleInputPassword1" class = "col-md-3">Password</label>
							    <div class = "col-md-9">
							    	<input type="password" class="form-control"  v-model="memberForm.user.password">
		                    		<span v-if="errors.user.password" class="help text-red">{{ errors.user.password[0] }}</span>
								</div>
							</div>
						</div>
								
						<div class="form-group">
							<div class = "row">
							    <label for="exampleInputPassword1" class = "col-md-3">Email</label>
							    <div class = "col-md-9">
							    	<input type="email" class="form-control"  v-model="memberForm.user.email">
		                    		<span v-if="errors.user.email" class="help text-red">{{ errors.user.email[0] }}</span>
								</div>
							</div>
						</div>

						<div class="form-group">
							<div class = "row">
								<label class = "col-md-3">Division</label>
								<div class = "col-md-9">
			                		<multiselect  v-model="memberForm.detail.division_id" :options="divisionList" :searchable="false" :close-on-select="true" :allow-empty="false" label="label" placeholder="Select one" track-by="value" >
									</multiselect>
		                    		<span v-if="errors.detail.division_id" class="help text-red">{{ errors.detail.division_id[0] }}</span>
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class = "row">
							    <label class = "col-md-3">Station</label>
							    <div class = "col-md-9">
				              		<multiselect  v-model="memberForm.detail.station_id" :options="stationList" :searchable="false" :close-on-select="true" :allow-empty="false" label="label" placeholder="Select one" track-by="value" >
									</multiselect>
		                    		<span v-if="errors.detail.station_id" class="help text-red">{{ errors.detail.station_id[0] }}</span>
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class = "row">
							    <label class = "col-md-3">Position</label>
							    <div class = "col-md-9">
							    	<input type="text" class="form-control" v-model = "memberForm.detail.position">
							    </div>
							</div>
						</div>			
					</div>

					<div class = "col-md-6">
						<div class="form-group">
							<div class = "row">
								<label class = "col-md-3">Member Type</label>
								<div class = "col-md-9">
			                		<multiselect  v-model="memberForm.detail.member_type_id" :options="typeList" :searchable="false" :close-on-select="true" :allow-empty="false" label="label" placeholder="Select one" track-by="value" >
									</multiselect>
		                    		<span v-if="errors.detail.member_type_id" class="help text-red">{{ errors.detail.member_type_id[0] }}</span>
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class = "row">
						    	<label class = "col-md-3">Membership Date</label>
							    <div class = "col-md-9">
						    		<date-picker v-model="memberForm.detail.mem_date" type="date" format="yyyy-MM-dd" lang="en"></date-picker>
		                    		<span v-if="errors.detail.mem_date" class="help text-red">{{ errors.detail.mem_date[0] }}</span>
						    	</div>
						    </div>
						</div>
						<div class="form-group">
							<div class = "row">
						    	<label class = "col-md-3">Birth date</label>
							    <div class = "col-md-9">
						    		<date-picker v-model="memberForm.detail.birthday" type="date" format="yyyy-MM-dd" lang="en"></date-picker>
		                    		<span v-if="errors.detail.birthday" class="help text-red">{{ errors.detail.birthday[0] }}</span>
						    	</div>
						    </div>
						</div>
						<div class="form-group">
							<div class = "row">
						    	<label class = "col-md-3">Gender</label>
							    <div class = "col-md-9">
						    		<multiselect  v-model="memberForm.detail.gender" :options="genderList" :searchable="false" :close-on-select="true" :allow-empty="false" label="label" placeholder="Select one" track-by="value" >
									</multiselect>
		                    		<span v-if="errors.detail.gender" class="help text-red">{{ errors.detail.gender[0] }}</span>
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class = "row">
						    	<label class = "col-md-3">Civil Status</label>
							    <div class = "col-md-9">
						    		<multiselect  v-model="memberForm.detail.civil_status" :options="statusList" :searchable="false" :close-on-select="true" :allow-empty="false" label="label" placeholder="Select one" track-by="value" >
									</multiselect>
		                    		<span v-if="errors.detail.civil_status" class="help text-red">{{ errors.detail.civil_status[0] }}</span>
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class = "row">
						    	<label class = "col-md-3">Salary</label>
							    <div class = "col-md-9">
						    		<input type="number" class="form-control" v-model="memberForm.detail.salary">
						    	</div>
						    </div>
						</div>
						<div class="form-group">
							<div class = "row">
						    	<label class = "col-md-3">Tel. No</label>
							    <div class = "col-md-9">
						    		<input type="text" class="form-control" v-model = "memberForm.detail.telephone">
						    	</div>
						    </div>
						</div>
					</div>
					<div class = "col-md-12" style = "margin-top:20px;">
					</div>
					<div class = "col-md-6">								
						<label>Primary Address:</label>
						<div class="form-group">
							<div class = "row">
						    	<label class = "col-md-3">Address</label>
							    <div class = "col-md-9">
								    <input type="text" class="form-control" v-model = "memberForm.address.address" >
		                    		<span v-if="errors.address.address" class="help text-red">{{ errors.address.address[0] }}</span>
						    	</div>
						    </div>
						</div>	
						<div class="form-group">
							<div class = "row">
						    	<label class = "col-md-3">City</label>
							    <div class = "col-md-9">
								    <input type="text" class="form-control" v-model = "memberForm.address.city" >
						    	</div>
						    </div>
						</div>	
						<div class="form-group">
							<div class = "row">
						    	<label class = "col-md-3">Province</label>
							    <div class = "col-md-9">
								    <input type="text" class="form-control" v-model = "memberForm.address.province" >
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
								    <input type="text" class="form-control" v-model = "memberForm.family.name" >
		                    		<span v-if="errors.family.name" class="help text-red">{{ errors.family.name[0] }}</span>
								</div>
						    </div>
						</div>
						<div class = "form-group">
							<div class = "row">
						    	<label class = "col-md-3">Relation</label>
							    <div  class = "col-md-9">
								    <input type="text" class="form-control" v-model = "memberForm.family.relation" >
		                    		<span v-if="errors.family.relation" class="help text-red">{{ errors.family.relation[0] }}</span>
								</div>
						    </div>
						</div>
						<div class = "form-group">
							<div class = "row">
						    	<label class = "col-md-3">Address</label>
							    <div  class = "col-md-9">
								    <textarea v-model = "memberForm.family.address" class="form-control"></textarea>
								</div>
						    </div>
						</div>
						<div class = "form-group">
							<div class = "row">
						    	<label class = "col-md-3">Contact No.</label>
							    <div  class = "col-md-9">
								    <input type="text" class="form-control" v-model = "memberForm.family.contact_no" >
								</div>
						    </div>
						</div>
					</div>
					<div class = "col-md-12 content-btn">
						<button class = "btn btn-primary" title="Save Member" @click = "saveForm()">Save</button>
					</div>
				</div>
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
    import DataTable from 'vue-materialize-datatable'

export default {
	props: ['dataMember', 'dataUser', 'dataFamily', 'dataAddress', 'dataStationList', 'dataDivisionList', 'dataTypeList', 'dataMemberList', 'dataView', 'baseUrl'],
	data: function () {

		let gender = [{ value : "Male", label : "Male"}, { value : "Female", label : "Female"}]
		let status = [{ value : "Single", label : "Single"}, 
					{ value : "Married", label : "Married"}, 
					{ value : "Widow", label : "Widow"}, 
					{ value : "Separated", label : "Separated"}]

		let cols = [
				{
                    label: 'Name',
                    field: this.retFullName,
                    html: true,
                    export: true,
                },
				{
                    label: 'ID Number',
                    field: 'id',
                    html: true,
                    export: true,
                },
				{
                    label: 'Username',
                    field: 'user.username',
                    html: true,
                    export: true,
                },
				{
                    label: 'Email',
                    field: 'user.email',
                    html: true,
                    export: true,
                },
				{
                    label: 'Member Type',
                    field: 'memberType.description',
                    html: true,
                    export: true,
                },
				{
                    label: 'Station',
                    field: 'station.name',
                    html: true,
                    export: true,
                },
				{
                    label: 'Position',
                    field: 'position',
                    html: true,
                    export: true,
                },
				{
                    label: 'Division',
                    field: 'division.name',
                    html: true,
                    export: true,
                },
				{
                    label: 'Date Registered',
                    field: this.retDateRegistered,
                    html: true,
                    export: true,
                },
				{
                    label: 'Action',
                    field: this.retAction,
                    html: true,
                    export: false,
                },
        ]
		let tabId = ['general_info', 'member_details', 'member_details_2']

		return {
			memberForm 		: {detail : this.dataMember, user : this.dataUser, family : this.dataFamily, address : this.dataAddress},
			stationList 	: this.dataStationList,
			divisionList 	: this.dataDivisionList,
			typeList 		: this.dataTypeList,
			listMember 		: this.dataMemberList,
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
            pageView		: this.dataView,
            columnList		: cols
		}
	},
    components: {
        DatePicker,
        VueTimepicker,
        Multiselect,
        Tabs,
        Tab,
        datatable: DataTable
    }, 
    mounted: function () {
    	
    }, 
    computed: {
    },
    methods: {   
    	retDateRegistered(row){
    		return this.formatDate(row.mem_date, 'MMMM DD, YYYY')
    	},
    	retFullName(row){
    		return row.first_name + ' '+ row.middle_name + ' '+  row.last_name
    	},
    	retAction(row){
    		return "<a href = '"+ this.baseUrl+"/member/view/" + row.id + "' target = '_blank' class = 'btn btn-xs btn-info'><i class='fa fa-fw fa-eye'></i></a>"
    	},
    	getViewTitle(){
			if(this.pageView == 'member-create'){
				return "Create Member"
			}
			else if(this.pageView == 'member-list'){
				return "Member List"
			}
    	},	
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

    		let new_member = this.memberForm

    		if(vm.memberForm.birthday != null){
    			new_member.detail.birthday = vm.formatDate(vm.memberForm.detail.birthday, "YYYY-MM-DD")
    		}
    		new_member.detail.mem_date = vm.formatDate(vm.memberForm.detail.mem_date, "YYYY-MM-DD")
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

            		vm.listMember = res.data
            		vm.pageView = 'member-list'
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
	                type: type ,
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