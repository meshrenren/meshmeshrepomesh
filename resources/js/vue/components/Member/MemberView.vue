<template>
	<div class="member-view">
		<div class = "row">
			<div class = "col-md-3">
				<div class = "box box-primary" id = "content-left">
					<div class = "box-body box-profile">
						<div class = "image-container">
							<div class="circle-avatar"></div>
						</div>
						<div class = "content-name">
							<h3 class = "profile-username text-center">
								<span class = "svg_edit" v-html = "svg_update" @click = "showToggle('detailName', true, 'name')"></span>
								{{ member.first_name }} {{ member.middle_name }} {{ member.last_name }}
							</h3>
							<div class = "float-multiselect" id = "detailName">
								<input title="Press enter to save" type="text" class = "form-control"
									:value="member.first_name" 
									ref="detailFirstname"
									placeholder = "First Name (Press enter to save)" 
									v-on:keyup.enter = "updateMember(this, 'detailFirstname', 'first_name', 'member')" >
								<input title="Press enter to save" type="text" class = "form-control"
									:value="member.middle_name" 
									ref="detailMiddlename"
									placeholder = "Middle Name (Press enter to save)" 
									v-on:keyup.enter = "updateMember(this, 'detailMiddlename', 'middle_name', 'member')" >
								<input title="Press enter to save" type="text" class = "form-control"
									:value="member.last_name" 
									ref="detailLastname"
									placeholder = "Last Name (Press enter to save)" 
									v-on:keyup.enter = "updateMember(this, 'detailLastname', 'last_name', 'member')" >
								<span class = "close" @click = "showToggle('detailName', false)">Close</span>
							</div>
						</div>
						<ul class = "list-group list-group-unbordered">
							<li class = "list-group-item">
								<b>Username</b>
								<span class = "pull-right">{{ member.username }}</span>
								<span class = "svg_edit pull-right" v-html = "svg_update" @click = "showToggle('detailUsername', true, 'username')"></span>
								<div class = "float-multiselect" id = "detailUsername">
									<input title="Press enter to save" type="text" class = "form-control"
										:value="member.username" 
										ref="detailUsername"
										placeholder = "Username (Press enter to save)" 
										v-on:keyup.enter = "updateMember(this, 'detailUsername', 'username', 'user')" >
									<span class = "close" @click = "showToggle('detailUsername', false)">Close</span>
								</div>
							</li>
							<li class = "list-group-item">
								<b>Email</b>
								<span class = "pull-right">{{ member.email }}</span>
								<span class = "svg_edit pull-right" v-html = "svg_update" @click = "showToggle('detailEmail', true, 'email')"></span>
								<div class = "float-multiselect" id = "detailEmail">
									<input title="Press enter to save" type="text" class = "form-control"
										:value="member.email" 
										ref="detailEmail"
										placeholder = "Username (Press enter to save)" 
										v-on:keyup.enter = "updateMember(this, 'detailEmail', 'email', 'user')" >
									<span class = "close" @click = "showToggle('detailEmail', false)">Close</span>
								</div>
							</li>
						</ul>
					</div>
				</div>
			</div>
			<div class = "col-md-9">
				<div class = "nav-tabs-custom" id = "content-right">
					<ul class = "nav nav-tabs">
						<li class="active"><a href="#general" data-toggle="tab">General Info</a></li>
						<li><a href="#address" data-toggle="tab">Address</a></li>
						<li><a href="#family" data-toggle="tab">Family</a></li>
					</ul>
					<div class = "tab-content">
						<div class = "active tab-pane" id = "general">
							<div class = "row">
								<div class = "col-md-6">
									<table class = "table detail-view">
										<tbody>
											<tr>
												<th>Member Type</th>
												<td class = "action-column">
													<span class = "svg_edit" v-html = "svg_update" @click = "showToggle('detailType', true)"></span>
												</td>
												<td>
													<span>{{ member.memberType.description }}</span>
													<div class = "float-multiselect" id = "detailType">
														<multiselect  @select="selectChange" v-model="typeVal" :options="typeList" :searchable="false" :close-on-select="true" :allow-empty="false" label="label" placeholder="Select one" track-by="value" >
														</multiselect>
														<span class = "close" @click = "showToggle('detailType', false)">Close</span>
													</div>
												</td>
											</tr>
											<tr>
												<th>Station</th>
												<td class = "action-column">
													<span class = "svg_edit" v-html = "svg_update" @click = "showToggle('detailStation', true)"></span>
												</td>
												<td>
													<span>{{ member.station.name }}</span>
													<div class = "float-multiselect" id = "detailStation">
														<multiselect  @select="selectChange" v-model="stationVal" :options="stationList" :searchable="false" :close-on-select="true" :allow-empty="false" label="label" placeholder="Select one" track-by="value" >
														</multiselect>
														<span class = "close" @click = "showToggle('detailStation', false)">Close</span>
													</div>
												</td>
											</tr>
											<tr>
												<th>Division</th>
												<td class = "action-column">
													<span class = "svg_edit" v-html = "svg_update" @click = "showToggle('detailDivision', true)"></span>
												</td>
												<td>
													<span>{{ member.division.name }}</span>
													<div class = "float-multiselect" id = "detailDivision">
														<multiselect  @select="selectChange" v-model="divisionVal" :options="divisionList" :searchable="false" :close-on-select="true" :allow-empty="false" label="label" placeholder="Select one" track-by="value" >
														</multiselect>
														<span class = "close" @click = "showToggle('detailDivision', false)">Close</span>
													</div>
												</td>
											</tr>
											<tr>
												<th>Position</th>
												<td class = "action-column">
													<span class = "svg_edit" v-html = "svg_update" @click = "showToggle('detailPosition', true, 'position')"></span>
												</td>
												<td>
													<span>{{ member.position }}</span>
													<div class = "float-multiselect" id = "detailPosition">
														<input title="Press enter to save" type="text" class = "form-control"
															:value="member.position" 
															ref="detailPosition"
															placeholder = "Position (Press enter to save)" 
															v-on:keyup.enter = "updateMember(this, 'detailPosition', 'position', 'member')" >
														<span class = "close" @click = "showToggle('detailPosition', false)">Close</span>
													</div>
												</td>
											</tr>
											<tr>
												<th>Membership Date</th>
												<td class = "action-column">
													<span class = "svg_edit" v-html = "svg_update" @click = "showToggle('detailMemdate', true)"></span>
												</td>
												<td>
													<span>{{ formatDate(member.mem_date, 'MMMM DD, YYYY') }}</span>
													<div class = "float-multiselect" id = "detailMemdate">
														<date-picker :value="member.mem_date" ref = "detailMemdate" type="date" format="yyyy-MM-dd" lang="en" confirm @confirm="updateMember(this, 'detailMemdate', 'mem_date', 'member')"></date-picker>
														<span class = "close" @click = "showToggle('detailMemdate', false)">Close</span>
													</div>
												</td>
											</tr>
											<tr>
												<th>Birthdate</th>
												<td class = "action-column">
													<span class = "svg_edit" v-html = "svg_update" @click = "showToggle('detailBirthday', true)"></span>
												</td>
												<td>
													<span>{{ formatDate(member.birthday, 'MMMM DD, YYYY') }}</span>
													<div class = "float-multiselect" id = "detailBirthday">
														<date-picker :value="member.birthday" ref = "detailBirthday" type="date" format="yyyy-MM-dd" lang="en" confirm  @confirm="updateMember(this, 'detailBirthday', 'birthday', 'member')"></date-picker>
														<span class = "close" @click = "showToggle('detailBirthday', false)">Close</span>
													</div>
												</td>
											</tr>
										</tbody>
									</table>
								</div>								
								<div class = "col-md-6">
									<table class = "table detail-view">
										<tbody>

											<tr>
												<th>Gender</th>
												<td class = "action-column">
													<span class = "svg_edit" v-html = "svg_update" @click = "showToggle('detailGender', true)"></span>
												</td>
												<td>
													<span>{{ member.gender }}</span>
													<div class = "float-multiselect" id = "detailGender">
														<multiselect  @select="selectChange" v-model="genderVal" :options="genderList" :searchable="false" :close-on-select="true" :allow-empty="false" label="label" placeholder="Select one" track-by="value" >
														</multiselect>
														<span class = "close" @click = "showToggle('detailGender', false)">Close</span>
													</div>
												</td>
											</tr>
											<tr>
												<th>Civil Status</th>
												<td class = "action-column">
													<span class = "svg_edit" v-html = "svg_update" @click = "showToggle('detailStatus', true)"></span>
												</td>
												<td>
													<span>{{ member.civil_status }}</span>
													<div class = "float-multiselect" id = "detailStatus">
														<multiselect  @select="selectChange" v-model="statusVal" :options="statusList" :searchable="false" :close-on-select="true" :allow-empty="false" label="label" placeholder="Select one" track-by="value" >
														</multiselect>
														<span class = "close" @click = "showToggle('detailStatus', false)">Close</span>
													</div>
												</td>
											</tr>
											<tr>
												<th>Employee Number</th>
												<td class = "action-column">
													<span class = "svg_edit" v-html = "svg_update" @click = "showToggle('detailEmpnumber', true, 'employee_no')"></span>
												</td>
												<td>
													<span>{{ member.employee_no }}</span>
													<div class = "float-multiselect" id = "detailEmpnumber">
														<input title="Press enter to save" type="text" class = "form-control"
															:value="member.position" 
															ref="detailEmpnumber"
															placeholder = "Employee Number (Press enter to save)" 
															v-on:keyup.enter = "updateMember(this, 'detailEmpnumber', 'employee_no', 'member')" >
														<span class = "close" @click = "showToggle('detailEmpnumber', false)">Close</span>
													</div>
												</td>
											</tr>
											<tr>
												<th>Salary</th>
												<td class = "action-column">
													<span class = "svg_edit" v-html = "svg_update" @click = "showToggle('detailSalary', true, 'salary')"></span>
												</td>
												<td>
													<span v-html = "member.salary == null ? '' : parseFloat(member.salary).toFixed(2)"></span>
													<div class = "float-multiselect" id = "detailSalary">
														<input title="Press enter to save" type="number" class = "form-control"
															:value="member.position" 
															ref="detailSalary"
															placeholder = "Salary (Press enter to save)" 
															v-on:keyup.enter = "updateMember(this, 'detailSalary', 'salary', 'member')" >
														<span class = "close" @click = "showToggle('detailSalary', false)">Close</span>
													</div>
												</td>
											</tr>
											<tr>
												<th>GSIS Number</th>
												<td class = "action-column">
													<span class = "svg_edit" v-html = "svg_update" @click = "showToggle('detailGsisno', true, 'gsis_no')"></span>
												</td>
												<td>
													<span>{{ member.gsis_no }}</span>
													<div class = "float-multiselect" id = "detailGsisno">
														<input title="Press enter to save" type="text" class = "form-control"
															:value="member.position" 
															ref="detailGsisno"
															placeholder = "GSIS Number (Press enter to save)" 
															v-on:keyup.enter = "updateMember(this, 'detailGsisno', 'gsis_no', 'member')" >
														<span class = "close" @click = "showToggle('detailGsisno', false)">Close</span>
													</div>
												</td>
											</tr>
											<tr>
												<th>Telephone</th>
												<td class = "action-column">
													<span class = "svg_edit" v-html = "svg_update" @click = "showToggle('detailTelephone', true, 'telephone')"></span>
												</td>
												<td>
													<span>{{ member.gsis_no }}</span>
													<div class = "float-multiselect" id = "detailTelephone">
														<input title="Press enter to save" type="text" class = "form-control"
															:value="member.position" 
															ref="detailTelephone"
															placeholder = "Telephone (Press enter to save)" 
															v-on:keyup.enter = "updateMember(this, 'detailTelephone', 'telephone', 'member')" >
														<span class = "close" @click = "showToggle('detailTelephone', false)">Close</span>
													</div>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div class = "tab-pane" id = "address">
							This is Address
						</div>
						<div class = "tab-pane" id = "family">
							This is Family
						</div>
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
    import DataTable from 'vue-materialize-datatable'
    import swal from 'sweetalert2/dist/sweetalert2.all.min.js'

    import { SweetModal } from 'sweet-modal-vue' 

export default {
	props: ['dataMember', 'dataStationList', 'dataDivisionList', 'dataTypeList', 'baseUrl'],
	data: function () {

		let gender = [{ value : "Male", label : "Male", column_name : 'gender'}, { value : "Female", label : "Female", column_name : 'gender'}]
		let status = [{ value : "Single", label : "Single", column_name : 'civil_status'}, 
					{ value : "Married", label : "Married", column_name : 'civil_status'}, 
					{ value : "Widow", label : "Widow", column_name : 'civil_status'}, 
					{ value : "Separated", label : "Separated", column_name : 'civil_status'}]

		return {
			member 			: this.dataMember,
			stationList 	: this.dataStationList,
			divisionList 	: this.dataDivisionList,
			typeList 		: this.dataTypeList,
			genderList		: gender,
			statusList		: status,
  			stationVal 		: {value : this.dataMember.station_id, label : this.dataMember.station.name, column_name : 'station_id'},
  			divisionVal 	: {value : this.dataMember.division_id, label : this.dataMember.division.name, column_name : 'division_id'},
  			typeVal 		: {value : this.dataMember.member_type_id, label : this.dataMember.memberType.description, column_name : 'member_type_id'},
  			genderVal 		: {value : this.dataMember.gender, label : this.dataMember.gender, column_name : 'gender'},
  			statusVal 		: {value : this.dataMember.civil_status, label : this.dataMember.civil_status, column_name : 'civil_status'},
			svg_update		: "<i class='fa fa-fw fa-pencil'></i>",
			svg_delete		: "<i class='fa fa-fw fa-times'></i>",
		}
	},
	components: {
	    SweetModal,
        DatePicker,
        datatable: DataTable,
        Multiselect,
        Tabs,
        Tab,
    },
	mounted: function ()
	{  		
		var vm=this;

		let rightBox = document.getElementById('content-right')
		let leftBox = document.getElementById('content-left')

		let leftHeight = leftBox.offsetHeight
		rightBox.style.minHeight = leftHeight + 'px'
	},
    methods: {  	
        formatDate(date, format){
          	if(date){
            	return moment(date).format(format)
          	}
          	return null
        },
    	showToggle(ref, state, detail = null){
    		if(detail != null){
    			if(detail == 'name'){
    				this.$refs['detailFirstname'].value = this.member.first_name
    				this.$refs['detailMiddlename'].value = this.member.middle_name
    				this.$refs['detailLastname'].value = this.member.last_name
    			}
    			else{
    				this.$refs[ref].value = this.member[detail]
    			}
    			
    		}
    		let floats = document.getElementsByClassName('float-multiselect')
    		for (var i =  0; i < floats.length; i++) {
    			if(floats[i].id != ref){
    				floats[i].style.display = 'none'
    			}
    		}

    		if(state){
    			document.getElementById(ref).style.display = 'block'
    		}
    		else{
    			document.getElementById(ref).style.display = 'none'
    		}
    	} ,
    	selectChange(data){
    		let value = data.value
    		let label = data.column_name
    		this.updateDetail(label, value, 'member')
    		let ref = ""
    		if(label == 'station_id'){ ref = 'detailStation'} 
    		else if(label == 'division_id'){ ref = 'detailDivision'}
    		else if(label == 'member_type_id'){ ref = 'detailType'}
    		else if(label == 'gender'){ ref = 'detailGender'}
    		else if(label == 'civil_status'){ ref = 'detailStatus'}

    		this.showToggle(ref, false)
    	},
    	updateMember(env, ref, label, table ){
    		let value = this.$refs[ref].value
    		if(label == "mem_date" || label == "birthday"){
    			value = this.$refs[ref].currentValue
    			value = this.formatDate(value, 'YYYY-MM-DD')
    		}
    		console.log(value)

    		this.updateDetail(label, value, table)

    		if(label != 'first_name' && label != 'middle_name' && label != 'last_name' ){
    			this.showToggle(ref, false)
    		}

    	},
    	updateDetail(label, value, table){
    		let vm = this

    		let data = new FormData()

    		data.set('member_id', vm.member.id)
    		data.set('user_id', vm.member.user_id)
    		data.set('table', table)
    		data.set('label', label)
    		data.set('value', value)

            axios.post(this.baseUrl+'/member/update-member', data).then((result) => {
            	let res = result.data
            	let type = ""
            	let message = ""
            	console.log(res)
            	if(res.success == true){
            		type = "success"
            		message = "Member successfully update."

            		vm.member = res.data
            	}
            	else{
            		console.log(res.status)
            		if(res.status == 'has-error'){
            			type = "warning"
	            		message = res.error[label][0]
            		}
            		else{
	            		type = "error"
	            		message = "Member not updated. Please try again or contact administrator."
	            	}
            	}            	

            	new Noty({
	                theme: 'relax',
	                type: type ,
	                layout: 'topRight',
	                text: message,
	                timeout: 2500
	            }).show();

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