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
					<div class = "table-toolbar">
						<div>
							<el-input type="text" v-model="search" placeholder = "Search member name"></el-input>
						</div>
						<div>
							<el-button type="primary" @click = "pageView = 'member-create'">Create Member</el-button>
						</div>
					</div>
					<div class = "member-table">
						<datatable ref="memberTable" title="" :perPage="[10, 25, 50, 100]" :exportable="false" :printable="false" :columns="columnList" :rows="memberListData" :sortable="false" :searchable="false" :exactSearch="false" >
						</datatable>
					</div>
				</div>
				<div class = "row member-create" v-if = "pageView == 'member-create'">
					<el-form :model="memberForm" :rules="rules" ref="memberForm" label-width="160px" class="demo-ruleForm">
						<div class = "col-md-6">

							<el-form-item label="First Name" prop="first_name" ref="first_name">
						    	<el-input type="text" v-model="memberForm.first_name"></el-input>
						  	</el-form-item>

						  	<el-form-item label="Middle Name" prop="middle_name" ref="middle_name">
						    	<el-input type="text" v-model="memberForm.middle_name"></el-input>
						  	</el-form-item>

						  	<el-form-item label="Last Name" prop="last_name" ref="last_name">
						    	<el-input type="text" v-model="memberForm.last_name"></el-input>
						  	</el-form-item>

						  	<el-form-item label="Username" prop="username" ref="username">
						    	<el-input type="text" v-model="memberForm.username"></el-input>
						  	</el-form-item>

						  	<el-form-item label="Password" prop="password" ref="password">
						    	<el-input type="password" v-model="memberForm.password"></el-input>
						  	</el-form-item>

						  	<el-form-item label="Email" prop="email" ref="email">
						    	<el-input type="text" v-model="memberForm.email"></el-input>
						  	</el-form-item>

						  	<el-form-item label="Division" prop="division_id" ref="division_id">
						  		<el-select v-model="memberForm.division_id" filterable placeholder="Select">
								    <el-option
								      v-for="item in divisionList" :key="item.value" :label="item.label" :value="item.value">
								    </el-option>
								</el-select>
						  	</el-form-item>

						  	<el-form-item label="Station" prop="station_id" ref="station_id">
						  		<el-select v-model="memberForm.station_id" filterable placeholder="Select">
								    <el-option
								      v-for="item in stationList" :key="item.value" :label="item.label" :value="item.value">
								    </el-option>
								</el-select>
						  	</el-form-item>

						  	<el-form-item label="Position" prop="position" ref="position">
						  		<el-input type="text" v-model="memberForm.position"></el-input>
						  	</el-form-item>		
						</div>

						<div class = "col-md-6">
						  	<el-form-item label="Member Type" prop="member_type_id" ref="member_type_id">
						  		<el-select v-model="memberForm.member_type_id" filterable placeholder="Select">
								    <el-option
								      v-for="item in typeList" :key="item.value" :label="item.label" :value="item.value">
								    </el-option>
								</el-select>
						  	</el-form-item>

						  	<el-form-item label="Membership Date" prop="mem_date" ref="mem_date">
						  		<el-date-picker v-model="memberForm.mem_date" type="date" placeholder="Pick a date"> </el-date-picker>
						  	</el-form-item>

						  	<el-form-item label="Membership Date" prop="birthday" ref="birthday">
						  		<el-date-picker v-model="memberForm.birthday" type="date" placeholder="Pick a date"> </el-date-picker>
						  	</el-form-item>

						  	<el-form-item label="Position" prop="position" ref="position">
						  		<el-input type="text" v-model="memberForm.position"></el-input>
						  	</el-form-item>

						  	<el-form-item label="Gender" prop="gender" ref="gender">
						  		<el-select v-model="memberForm.gender" filterable placeholder="Select">
								    <el-option label="Male"  value="Male"> </el-option>
								    <el-option label="Female"  value="Female"> </el-option>
								</el-select>
						  	</el-form-item>

						  	<el-form-item label="Civil Status" prop="civil_status" ref="civil_status">
						  		<el-select v-model="memberForm.civil_status" filterable placeholder="Select">
								    <el-option
								      v-for="item in statusList" :key="item.value" :label="item.label" :value="item.value">
								    </el-option>
								</el-select>
						  	</el-form-item>

						  	<el-form-item label="Salary" prop="salary" ref="salary">
						  		<el-input-number v-model="memberForm.salary" controls-position="right" @change="handleChange" :min="0"></el-input-number>
						  	</el-form-item>	

						  	<el-form-item label="Tel. No" prop="telephone" ref="telephone">
						  		<el-input type="text" v-model="memberForm.telephone"></el-input>
						  	</el-form-item>
						</div>
						<div class = "col-md-12" style = "margin-top:20px;">
						</div>
						<div class = "col-md-6">								
							<label>Primary Address:</label>

						  	<el-form-item label="Address" prop="con_address" ref="con_address">
						  		<el-input type="text" v-model="memberForm.con_address"></el-input>
						  	</el-form-item>
							
						  	<el-form-item label="City" prop="city" ref="city">
						  		<el-input type="text" v-model="memberForm.city"></el-input>
						  	</el-form-item>	
							
						  	<el-form-item label="Province" prop="province" ref="province">
						  		<el-input type="text" v-model="memberForm.province"></el-input>
						  	</el-form-item>	
						</div>
						<div class = "col-md-6">
							<label>In case of Emergency:</label>
							
						  	<el-form-item label="Name" prop="name" ref="name">
						  		<el-input type="text" v-model="memberForm.name"></el-input>
						  	</el-form-item>	
							
						  	<el-form-item label="Relation" prop="relation" ref="relation">
						  		<el-input type="text" v-model="memberForm.relation"></el-input>
						  	</el-form-item>	
							
						  	<el-form-item label="Address" prop="family_address" ref="family_address">
						  		<el-input type="text" v-model="memberForm.fam_address"></el-input>
						  	</el-form-item>	
							
						  	<el-form-item label="Contact No." prop="contact_no" ref="contact_no">
						  		<el-input type="text" v-model="memberForm.contact_no"></el-input>
						  	</el-form-item>	

						</div>
					</el-form>
					<div class = "col-md-12 content-btn">
						<button class = "btn btn-primary" title="Save Member" @click = "saveForm()">Save</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<style lang="scss">
  	@import '../../assets/site.scss';
  	@import '../../assets/member.scss';
  	@import '~noty/src/noty.scss';
</style>

<script>

	window.noty = require('noty')

	import axios from 'axios'
    import Noty from 'noty'
    import cloneDeep from 'lodash/cloneDeep'  
    import DataTable from 'vue-materialize-datatable'
    import merge from 'lodash/merge'

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
				/*{
                    label: 'Date Registered',
                    field: this.retDateRegistered,
                    html: true,
                    export: true,
                },*/
				{
                    label: 'Action',
                    field: this.retAction,
                    html: true,
                    export: false,
                },
        ]
		let tabId = ['general_info', 'member_details', 'member_details_2']


		return {
			memberForm 		: merge(this.dataMember, this.dataUser, this.dataFamily, this.dataAddress),
			rules 			: {},
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
            columnList		: cols,
            search 			: ""
		}
	},
	computed:{
		memberListData(){
			let list = cloneDeep(this.listMember)
			let sMember = this.search

			if(sMember){
				list = list.filter(data => { 
					let fullname = data.first_name + ' '+ data.middle_name + ' '+  data.last_name
					return fullname.toLowerCase().includes(sMember.toLowerCase()) 
				})
			}
			return list
		}
	},
    components: {
        datatable: DataTable
    }, 
    mounted: function () {
    	
    }, 
    created() {
    	this.rules = {
    		first_name : [ { required: true, message: 'First Name cannot be blank.', trigger: 'blur' }
    		],
    		last_name : [ { required: true, message: 'Last Name cannot be blank.', trigger: 'blur' }
    		],
    		middle_name : [ { required: true, message: 'Middle Name cannot be blank', trigger: 'blur' }
    		],
    		username : [ { required: true, message: 'Username cannot be blank', trigger: 'blur' }
    		],
    		password : [ { required: true, message: 'Password cannot be blank', trigger: 'blur' }
    		],
    		email : [ { required: true, message: 'Email cannot be blank', trigger: 'blur' },
     			{ type: 'email', message: 'Email is not a valid email address.', trigger: 'blur,change' }
    		],
    		division_id : [ { required: true, message: 'Division cannot be blank', trigger: 'blur' }
    		],
    		station_id : [ { required: true, message: 'Station cannot be blank', trigger: 'blur' }
    		],
    		member_type_id : [ { required: true, message: 'Member Type cannot be blank', trigger: 'blur' }
    		],
    		mem_date : [ { required: true, message: 'Member Date cannot be blank', trigger: 'blur' }
    		],
    		con_address : [ { required: true, message: 'Address cannot be blank', trigger: 'blur' }
    		],
    		name : [ { required: true, message: 'Name cannot be blank', trigger: 'blur' }
    		],

    	}
    },
    methods: {   
    	handleChange(){

    	},
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
    		let vm = this

    		this.$refs.memberForm.validate((valid) => {
	          	if (valid) {
	          		let main_preloader = document.getElementById('main_preloader');
			        main_preloader.style.display = 'block';
			        let inf_detail = {}
			        let inf_user = {}
			        let inf_family = {}
			        let inf_address = {}

			        Object.keys(this.dataMember).forEach(item => {
			        	inf_detail[item] = this.memberForm[item]
			        });
			        Object.keys(this.dataUser).forEach(item => {
			        	inf_user[item] = this.memberForm[item]
			        });
			        Object.keys(this.dataAddress).forEach(item => {
			        	inf_address[item] = this.memberForm[item]
			        });
			        Object.keys(this.dataFamily).forEach(item => {
			        	inf_family[item] = this.memberForm[item]
			        });

		    		let new_member = {detail : inf_detail, user : inf_user, family : inf_family, address : inf_address}

		    		if(vm.memberForm.birthday != null){
		    			new_member.detail.birthday = vm.formatDate(vm.memberForm.birthday, "YYYY-MM-DD")
		    		}
		    		new_member.detail.mem_date = vm.formatDate(vm.memberForm.mem_date, "YYYY-MM-DD")
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
			            		let detail = res.error.detail
			            		let user = res.error.user
			            		let address = res.error.address
			            		let family = res.error.family
			            		if(Object.keys(detail).length > 0){
				            		Object.keys(detail).forEach(function(key) {
				            			vm.$refs[key].validateMessage = detail[key][0]
				            			vm.$refs[key].validateState = "error"
									});
				            	}
			            		if(Object.keys(user).length > 0){
				            		Object.keys(user).forEach(function(key) {
				            			vm.$refs[key].validateMessage = user[key][0]
				            			vm.$refs[key].validateState = "error"
									});
				            	}
			            		if(Object.keys(address).length > 0){
				            		Object.keys(address).forEach(function(key) {
				            			vm.$refs[key].validateMessage = address[key][0]
				            			vm.$refs[key].validateState = "error"
									});
				            	}
			            		if(Object.keys(family).length > 0){
				            		Object.keys(family).forEach(function(key) {
				            			vm.$refs[key].validateMessage = family[key][0]
				            			vm.$refs[key].validateState = "error"
									});
				            	}
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
	          	} else {

	            	return false;
	          	}
	        });

	    	

    	}

    }
}

</script>