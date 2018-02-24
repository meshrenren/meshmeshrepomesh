<template>
	<div class="member-form">
		<tabs>
			<tab name="General Info">
				<div class = "row">
					<div class = "col-md-6">
						<div class="form-group">
						    <label for="exampleInputPassword1">Name</label>
						    <input type="text" class="form-control" placeholder = "First Name">
						    <input type="text" class="form-control" placeholder = "Middle Name">
						    <input type="text" class="form-control" placeholder = "Last Name">
						</div>
						<div class="form-group">
						    <label for="exampleInputPassword1">Position</label>
						    <input type="text" class="form-control">
						</div>
						<div class="form-group">
						    <label for="exampleInputPassword1">Division</label>
						    <input type="text" class="form-control">
						</div>
						<div class="form-group">
						    <label for="exampleInputPassword1">Station</label>
						    <input type="text" class="form-control">
						</div>
						<div class="form-group">
						    <label for="exampleInputPassword1">Tel. No</label>
						    <input type="text" class="form-control">
						</div>
					</div>
					<div class = "col-md-6">
						<div class="form-group">
						    <label for="exampleInputPassword1">Birth date</label>
						    <date-picker v-model="birthdateVal" type="date" format="yyyy-MM-dd" lang="en"></date-picker>
						</div>
						<div class="form-group">
						    <label for="exampleInputPassword1">Gender</label>
                			<multiselect  v-model="genderVal" :options="genderList" :searchable="false" :close-on-select="true" :allow-empty="false" label="label" placeholder="Select one" track-by="value" >
							</multiselect>
						</div>
						<div class="form-group">
						    <label for="exampleInputPassword1">Civil Status</label>
                			<multiselect  v-model="statusVal" :options="statusList" :searchable="false" :close-on-select="true" :allow-empty="false" label="label" placeholder="Select one" track-by="value" >
							</multiselect>
						</div>
						<div class="form-group">
						    <label for="exampleInputPassword1">Salary</label>
						    <input type="number" class="form-control">
						</div>
					</div>
				</div>
			</tab>
		</tabs>
	</div>
</template>

<style lang="scss">
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
    import {Tabs, Tab} from 'vue-tabs-component';

export default {
	props: [],
	data: function () {

		let gender = [{ value : "Male", label : "Male"}, { value : "Female", label : "Female"}]
		let status = [{ value : "Single", label : "Single"}, 
					{ value : "Married", label : "Married"}, 
					{ value : "Widow", label : "Widow"}, 
					{ value : "Separated", label : "Separated"}]
		return {
			birthdateVal	: moment().format('YYYY-MM-DD'),
			genderVal		: gender[0],
			genderList		: gender,
			statusVal		: status[0],
			statusList		: status,
		}
	},
    components: {
        DatePicker,
        VueTimepicker,
        Multiselect,
        Tabs,
        Tab
    }   
}

</script>