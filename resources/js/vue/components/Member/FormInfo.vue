<template>	
	<div class = "form-info">
		<span class = "span-link" @click = "showFloat">{{ textValue }}</span>
		<div class="form-update" v-if = "isShowFloat">
			<el-form :model="formModel" ref="ruleForm" position = "top" @submit.native.prevent>
			  	<el-form-item :label="label" prop="value" ref = "form_value">
			  		<!-- If type is 'text' or 'email' -->
			    	<el-input v-if = "type == 'text' || type == 'email'" v-model="formModel.value" placeholder="Press enter to save" @keyup.enter.native="updateInfo" @blur = "isShowFloat = false"></el-input>
					
					<!-- If type is 'select' -->
			    	<el-select v-if = "type == 'select'" v-model="formModel.value" placeholder="Select" @change = "updateInfo">
					    <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value">
					    </el-option>
					</el-select>

					<!-- If type is 'date' -->
			    	<el-date-picker v-if = "type == 'date'" v-model="formModel.value" type="date" placeholder="Pick a date"  @change = "updateInfo" @keyup.enter.native="updateInfo"  @blur = "isShowFloat = false"> </el-date-picker>
					
					<!-- If type is 'number' -->
			    	<el-input-number v-if = "type == 'number'" v-model="formModel.value" controls-position="right" @change="updateInfo" :min="0"  @keyup.enter.native="updateInfo" @blur = "isShowFloat = false"></el-input-number>

			  	</el-form-item>
			</el-form>
			<span class = "close" @click = "isShowFloat = false">Close</span>
		</div>
	</div>
</template>
<script>
export default {
	props: {
		props: {
	        type: String,
	        default: ''
	    },
		text: {
	        type: String,
	        default: ''
	    },
		value: {
	        type: String,
	        default: ''
	    },
		label: {
	        type: String,
	        default: 'Enter value'
	    },
		type: {
	        type: String,
	        default: 'text'
	    },
		options: {
	        type: Array,
	        default: function () { return [] }
	    },
		otherDetails: {
	        type: Object,
	        default: function () { return {} }
	    },
	},
	data: function () {
		return{
			formModel 	: {value : this.value},
			isShowFloat : false,
			otherInfo 	: this.otherDetails
		}
	},
	computed: {		
		textValue(){
			let txt = this.value
			if(this.text)
				txt = this.text

			return txt
		}
	},
	methods:{
		handleChange(){

		},
		showFloat(){
			console.log(this.isShowFloat)
			this.isShowFloat = true
		},
		updateInfo(){
			let data = {}
			data['label'] = this.props
			data['value'] = this.formModel.value
			data['details']	= this.otherInfo
			this.$emit('update', data)
			this.isShowFloat = false
		}
	}
}
</script>

<style lang="scss">
.span-link{
	padding: 0px 4px;
    border-bottom: 1px dashed #3c8dbc;
    cursor: pointer;
}
.form-update{
  	position: absolute;
  	z-index: 2;
  	width: 100%; 
  	background-color: #fff;
    padding: 0px 5px;

  	.mx-datepicker{
    	width: 100% !important;
  	}

  	.close{
	    position: absolute;
	    right: 3px;
	    font-size: 12px;
	    cursor: pointer;
	    top: 5px !important;
  	}

}
.el-form{
	.el-form-item__label{
    	line-height: 20px;
		margin-bottom: 0px;
	}
}
</style>