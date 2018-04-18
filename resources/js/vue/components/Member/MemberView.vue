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

							<form-info ref = "first_name" :value = "member.first_name" props = "first_name" label = "First Name" @update = "updateMemberInfo"></form-info>

							<form-info ref = "middle_name" :value = "member.middle_name" props = "middle_name" label = "Middle Name" @update = "updateMemberInfo"></form-info>

							<form-info ref = "last_name" :value = "member.last_name" props = "last_name" label = "Last Name" @update = "updateMemberInfo"></form-info>
						</div>
						<ul class = "list-group list-group-unbordered">
							<li class = "list-group-item">
								<b>Username</b>
								<form-info ref = "username" :value = "member.username" props = "username" label = "Username" @update = "updateUserInfo"></form-info>
							</li>
							<li class = "list-group-item">
								<b>Email</b>
								<form-info ref = "email"  type = "email" :value = "member.email" props = "email" label = "Email" @update = "updateUserInfo"></form-info>
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
												<td>
													<form-info ref = "member_type_id" :text = "member.memberType.description" :value = "member.member_type_id" props = "member_type_id" label = "Member Type" type = "select" :options = "typeList" @update = "updateMemberInfo"></form-info>
												</td>
											</tr>
											<tr>
												<th>Station</th>
												<td>
													<form-info ref = "station_id" :text = "member.station.name" :value = "member.station_id" props = "station_id" label = "Station" type = "select" :options = "stationList" @update = "updateMemberInfo"></form-info>
												</td>
											</tr>
											<tr>
												<th>Division</th>
												<td>
													<form-info ref = "division_id" :text = "member.division.name" :value = "member.division_id" props = "division_id" label = "Division" type = "select" :options = "divisionList" @update = "updateMemberInfo"></form-info>
												</td>
											</tr>
											<tr>
												<th>Position</th>
												<td>
													<form-info ref = "position" :value = "member.position" props = "position" label = "Division" @update = "updateMemberInfo"></form-info>
												</td>
											</tr>
											<tr>
												<th>Membership Date</th>
												<td>
													<form-info ref = "mem_date" :text = "formatDate(member.mem_date, 'MMMM DD, YYYY')" :value = "member.mem_date" type = "date" props = "mem_date" label = "Membership Data" @update = "updateMemberInfo"></form-info>
												</td>
											</tr>
											<tr>
												<th>Birthdate</th>
												<td>
													<form-info ref = "birthday" :text = "formatDate(member.birthday, 'MMMM DD, YYYY')" :value = "member.birthday" type = "date" props = "birthday" label = "Birthdate" @update = "updateMemberInfo"></form-info>
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
												<td>
													<form-info ref = "gender" :text = "member.gender" :value = "member.gender" props = "gender" label = "Gender" type = "select" :options = "genderList" @update = "updateMemberInfo"></form-info>
												</td>
											</tr>
											<tr>
												<th>Civil Status</th>
												<td>
													<form-info ref = "civil_status" :text = "member.civil_status" :value = "member.civil_status" props = "civil_status" label = "Civil Status" type = "select" :options = "statusList" @update = "updateMemberInfo"></form-info>
												</td>
											</tr>
											<tr>
												<th>Employee Number</th>
												<td>
													<form-info ref = "employee_no" :value = "member.employee_no" props = "position" label = "Employee No." @update = "updateMemberInfo"></form-info>
												</td>
											</tr>
											<tr>
												<th>Salary</th>
												<td>
													<form-info ref = "salary" :value = "member.salary" props = "salary" label = "Salary" type = "number" @update = "updateMemberInfo"></form-info>
												</td>
											</tr>
											<tr>
												<th>GSIS Number</th>
												<td>
													<form-info ref = "gsis_no" :value = "member.gsis_no" props = "gsis_no" label = "GSIS No." @update = "updateMemberInfo"></form-info>
												</td>
											</tr>
											<tr>
												<th>Telephone</th>
												<td>
													<form-info ref = "telephone" :value = "member.telephone" props = "telephone" label = "Telephone" @update = "updateMemberInfo"></form-info>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div class = "tab-pane" id = "address">
							<member-address 
								:member-address = "memberAddress"
								:member-id="member.id"
							></member-address>
						</div>
						<div class = "tab-pane" id = "family">
							<member-family 
								:member-family = "memberFamily"
								:member-id="member.id"
							></member-family>
						</div>
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

.list-group-item{
	.form-info{
	    display: inline-block;
	    float: right;
	}
}
</style>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
<style src="vue-tabs-component/docs/resources/tabs-component.css"></style>

<script>

	window.noty = require('noty')

	import axios from 'axios'
    import Noty from 'noty'
    import DataTable from 'vue-materialize-datatable'
    import swal from 'sweetalert2/dist/sweetalert2.all.min.js'


    import FormInfo from './FormInfo'
	import MemberAddress from './ViewTab/MemberAddress'
	import MemberFamily from './ViewTab/MemberFamily'

export default {
	props: ['dataMember', 'dataStationList', 'dataDivisionList', 'dataTypeList', 'dataMemberFamily', 'dataMemberAddress', 'baseUrl'],
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
			memberFamily	: this.dataMemberFamily,
			memberAddress	: this.dataMemberAddress,
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
        FormInfo,
        MemberAddress,
        MemberFamily
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
    	testupdate(data){
    		console.log(data)
    	},
        formatDate(date, format){
          	if(date){
            	return moment(date).format(format)
          	}
          	return null
        },
        showToggleAddress(ref, index, state, detail = null){
        	if(detail != null){
        		this.$refs[ref][index].value = this.memberAddress[index][detail]
        	}
        	if(state){
    			document.getElementById(ref+'_'+index).style.display = 'block'
    		}
    		else{
    			document.getElementById(ref+'_'+index).style.display = 'none'
    		}
        },
        showToggleFamily(ref, index, state, detail = null){
        	if(detail != null){
        		this.$refs[ref][index].value = this.memberFamily[index][detail]
        	}
        	if(state){
    			document.getElementById(ref+'_'+index).style.display = 'block'
    		}
    		else{
    			document.getElementById(ref+'_'+index).style.display = 'none'
    		}
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
    		let floats = document.getElementsByClassName('float-update')
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
    	updateMemberAddress(env, ref, index, family_id, detail){

    	},
    	updateMemberFamily(env, ref, index, family_id, detail){
    		
    		let vm = this
    		let value = this.$refs[ref][index].value

    		let data = new FormData()

    		data.set('family_id', JSON.stringify(family_id))
    		data.set('label', detail)
    		data.set('value', value)
    		axios.post(this.baseUrl+'/member/update-family-member?da', data).then((result) => {
            	let res = result.data
            	let type = ""
            	let message = ""
            	console.log(res)
            	if(res.success == true){
            		type = "success"
            		message = "Family member successfully updated."

            		vm.memberFamily = res.data

            		vm.showToggleFamily(ref, index, false)
            	}
            	else{
            		type = "error"
	            	message = "Family Member not updated. Please try again or contact administrator."
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

    			if(error.response.status == 403)
    				location.reload()
  			})
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
            		message = "Member successfully updated."

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
    	},
    	updateAddressInfo(dataVal){
    		let vm = this

    		let data = new FormData()

    		data.set('family_id', JSON.stringify(family_id))
    		data.set('label', dataVal.label)
    		data.set('value', dataVal.value)
    		axios.post(this.baseUrl+'/member/update-family-member?', data).then((result) => {
            	let res = result.data
            	let type = ""
            	let message = ""
            	console.log(res)
            	if(res.success == true){
            		type = "success"
            		message = "Family member successfully updated."

            		vm.memberFamily = res.data
            	}
            	else{
            		vm.$refs[dataVal.label + "_" + family_id].$refs.form_value.validateMessage = res.error[dataVal.label][0]
	            	vm.$refs[dataVal.label + "_" + family_id].$refs.form_value.validateState = "error"
            	}          
	            
            }).catch(function (error) {

    			if(error.response.status == 403)
    				location.reload()
  			})
    	},
    	updateMemberInfo(dataVal){
    		let vm = this

    		if(dataVal.label == "mem_date" || dataVal.label == "birthday"){
    			dataVal.value = this.formatDate(dataVal.value, 'YYYY-MM-DD')
    		}

    		let data = new FormData()

    		data.set('member_id', vm.member.id)
    		data.set('user_id', vm.member.user_id)
    		data.set('table', 'member')
    		data.set('label', dataVal.label)
    		data.set('value', dataVal.value)

            axios.post(this.baseUrl+'/member/update-member', data).then((result) => {
            	let res = result.data
            	console.log(res)
            	if(res.success == true){
            		vm.$refs[dataVal.label].isShowFloat = false

            		vm.member = res.data
            	}
            	else{
            		console.log(res.status)
            		if(res.status == 'has-error'){
	            		vm.$refs[dataVal.label].$refs.form_value.validateMessage = res.error[dataVal.label][0]
	            		vm.$refs[dataVal.label].$refs.form_value.validateState = "error"
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

    			if(error.response.status == 403)
    				location.reload()
  			})
    	},
    	updateUserInfo(dataVal){
    		let vm = this

    		let data = new FormData() 

    		data.set('member_id', vm.member.id)
    		data.set('user_id', vm.member.user_id)
    		data.set('table', 'user')
    		data.set('label', dataVal.label)
    		data.set('value', dataVal.value)

            axios.post(this.baseUrl+'/member/update-member', data).then((result) => {
            	let res = result.data
            	console.log(res)
            	if(res.success == true){
            		vm.$refs[dataVal.label].isShowFloat = false

            		vm.member = res.data
            	}
            	else{
            		console.log(res.status)
            		if(res.status == 'has-error'){
	            		vm.$refs[dataVal.label].$refs.form_value.validateMessage = res.error[dataVal.label][0]
	            		vm.$refs[dataVal.label].$refs.form_value.validateState = "error"
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

    			if(error.response.status == 403)
    				location.reload()
  			})
    	}
    }
}
</script>