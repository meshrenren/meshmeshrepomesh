<template>
	<div class="member-form">
		<tabs ref = "tabsList" class = "member-tab">
			<tab name="General Info" id = "general_info">
				<div class = "row">
					<div class = "col-md-6">
						<div class="form-group">
							<div class = "row">
						   		<label for="exampleInputPassword1" class = "col-md-3" >Name</label>
							    <div  class = "col-md-7 input-name">
								    <input type="text" class="form-control" v-model = "member.detail.first_name" placeholder = "First Name" required >
								    <input type="text" class="form-control" v-model = "member.detail.middle_name" placeholder = "Middle Name" required >
								    <input type="text" class="form-control" v-model = "member.detail.last_name" placeholder = "Last Name" required >
								</div>
							</div>
                            <span v-show="errors.first_name" class="help is-danger">{{ errors.first_name }}</span>
						</div>
						
						<div class="form-group">
							<div class = "row">
							    <label for="exampleInputPassword1" class = "col-md-3">Username</label>
							    <div class = "col-md-9">
								    <input type="text" class="form-control"  v-model="member.user.username">
								</div>
							</div>
						</div>
						
						<div class="form-group">
							<div class = "row">
							    <label for="exampleInputPassword1" class = "col-md-3">Password</label>
							    <div class = "col-md-9">
							    	<input type="password" class="form-control"  v-model="member.user.password">
								</div>
							</div>
						</div>
						
						<div class="form-group">
							<div class = "row">
							    <label for="exampleInputPassword1" class = "col-md-3">Email</label>
							    <div class = "col-md-9">
							    	<input type="email" class="form-control"  v-model="member.user.email">
								</div>
							</div>
						</div>

						<div class="form-group">
							<div class = "row">
						    	<label class = "col-md-3">Division</label>
							    <div class = "col-md-9">
	                				<multiselect  v-model="member.detail.division_id" :options="divisionList" :searchable="false" :close-on-select="true" :allow-empty="false" label="label" placeholder="Select one" track-by="value" >
									</multiselect>
								</div>
							</div>
						</div>

						<div class="form-group">
							<div class = "row">
							    <label class = "col-md-3">Member Type</label>
							    <div class = "col-md-9">
	                				<multiselect  v-model="member.detail.member_type_id" :options="typeList" :searchable="false" :close-on-select="true" :allow-empty="false" label="label" placeholder="Select one" track-by="value" >
									</multiselect>
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
						<div class="form-group">
							<div class = "row">
							    <label class = "col-md-3">Station</label>
							    <div class = "col-md-9">
		                			<multiselect  v-model="member.detail.station_id" :options="stationList" :searchable="false" :close-on-select="true" :allow-empty="false" label="label" placeholder="Select one" track-by="value" >
									</multiselect>
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

					<div class = "col-md-6">
						<div class="form-group">
							<div class = "row">
						    	<label class = "col-md-3">Membership Date</label>
							    <div class = "col-md-9">
						    		<date-picker v-model="member.detail.mem_date" type="date" format="yyyy-MM-dd" lang="en"></date-picker>
						    	</div>
						    </div>
						</div>
						<div class="form-group">
							<div class = "row">
						    	<label class = "col-md-3">Birth date</label>
							    <div class = "col-md-9">
						    		<date-picker v-model="member.detail.birthday" type="date" format="yyyy-MM-dd" lang="en"></date-picker>
						    	</div>
						    </div>
						</div>
						<div class="form-group">
							<div class = "row">
						    	<label class = "col-md-3">Gender</label>
							    <div class = "col-md-9">
						    		<multiselect  v-model="member.detail.gender" :options="genderList" :searchable="false" :close-on-select="true" :allow-empty="false" label="label" placeholder="Select one" track-by="value" >
									</multiselect>
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class = "row">
						    	<label class = "col-md-3">Civil Status</label>
							    <div class = "col-md-9">
						    		<multiselect  v-model="member.detail.civil_status" :options="statusList" :searchable="false" :close-on-select="true" :allow-empty="false" label="label" placeholder="Select one" track-by="value" >
									</multiselect>
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
					</div>
					<div class = "col-md-12">
						<button class = "btn btn-primary" @click = "nextTab()">Next</button>
						<button class = "btn btn-primary" @click = "saveForm()">Save</button>
					</div>
				</div>
			</tab>

			<tab name="Member Details" id = "member_details">
				<div class = "row">
					<div class = "col-md-6">
						<div class="form-group">
						    <label for="exampleInputPassword1">Position</label>
						    <input type="text" class="form-control" v-model = "member.detail.position">
						</div>
						<div class="form-group">
						    <label for="exampleInputPassword1">Station</label>
                			<multiselect  v-model="member.detail.station_id" :options="stationList" :searchable="false" :close-on-select="true" :allow-empty="false" label="label" placeholder="Select one" track-by="value" >
							</multiselect>
						</div>
						<div class="form-group">
						    <label for="exampleInputPassword1">Tel. No</label>
						    <input type="text" class="form-control" v-model = "member.detail.telephone">
						</div>
					</div>
					<div class = "col-md-6">
						<div class="form-group">
						    <label for="exampleInputPassword1">Date of Membership</label>
						    <date-picker v-model="member.detail.mem_date" type="date" format="yyyy-MM-dd" lang="en"></date-picker>
						</div>
						<div class="form-group">
						    <label for="exampleInputPassword1">Birth date</label>
						    <date-picker v-model="member.detail.birthday" type="date" format="yyyy-MM-dd" lang="en"></date-picker>
						</div>
						<div class="form-group">
						    <label for="exampleInputPassword1">Gender</label>
                			<multiselect  v-model="member.detail.gender" :options="genderList" :searchable="false" :close-on-select="true" :allow-empty="false" label="label" placeholder="Select one" track-by="value" >
							</multiselect>
						</div>
						<div class="form-group">
						    <label for="exampleInputPassword1">Civil Status</label>
                			<multiselect  v-model="member.detail.civil_status" :options="statusList" :searchable="false" :close-on-select="true" :allow-empty="false" label="label" placeholder="Select one" track-by="value" >
							</multiselect>
						</div>
						<div class="form-group">
						    <label for="exampleInputPassword1">Salary</label>
						    <input type="number" class="form-control" v-model="member.detail.salary">
						</div>
					</div>
					<div class = "col-md-12">
						<button class = "btn btn-primary" @click = "previousTab()">Previous</button>
						<button class = "btn btn-primary" @click = "nextTab()">Next</button>
					</div>
				</div>
			</tab>

			<tab name="Member Details 2" id = "member_details_2">
				<div class = "row">
					This is a test 2
					<div class = "col-md-12">
						<button class = "btn btn-primary" @click = "previousTab()">Previous</button>
					</div>
				</div>
			</tab>
		</tabs>
		<vue-tabs>
			<v-tab title = "First Tab">
				First
			</v-tab>
			<v-tab title = "Second Tab">
				Second
			</v-tab>
			<v-tab title = "Third Tab">
				Third
			</v-tab>
		</vue-tabs>
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
	props: ['dataMember', 'dataUser', 'dataStationList', 'dataDivisionList', 'dataTypeList', 'dataBranchList', 'baseUrl'],
	data: function () {

		let gender = [{ value : "Male", label : "Male"}, { value : "Female", label : "Female"}]
		let status = [{ value : "Single", label : "Single"}, 
					{ value : "Married", label : "Married"}, 
					{ value : "Widow", label : "Widow"}, 
					{ value : "Separated", label : "Separated"}]
		let tabId = ['general_info', 'member_details', 'member_details_2']

		return {
			member 			: {detail : this.dataMember, user : this.dataUser},
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
			errors 			: {}
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
    	let tabs = document.querySelectorAll('.member-tab .tabs-component-tabs .tabs-component-tab')
    	console.log(tabs)
    	for (var i = 1; i < tabs.length; i++) {
    		console.log(tabs[i])
    		tabs[i].classList.add('is-disabled')
    		tabs[i].children[0].setAttribute('href', "#")
    		tabs[i].children[0].setAttribute('aria-controls', "#")
    	}
    },
    methods: {
    	nextTab(){
    		let tabs = this.$refs.tabsList
    		let index = this.currentTab + 1
    		tabs.$children[this.currentTab].isActive = false
    		tabs.$children[index].isActive = true

    		this.currentTab = index
    	},
    	previousTab(){
    		let tabs = this.$refs.tabsList
    		let index = this.currentTab - 1
    		tabs.$children[this.currentTab].isActive = false
    		tabs.$children[index].isActive = true

    		this.currentTab = index    		
    	},
    	saveForm(){
    		var data = new FormData()
    		console.log(this.member)
    		data.set('employee', JSON.stringify(this.member))

            axios.post(this.baseUrl+'/member/save-member', data).then((response) => {
            	console.log(response)

            })
    	}

    }
}

</script>