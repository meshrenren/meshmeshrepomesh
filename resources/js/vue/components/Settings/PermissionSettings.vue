<template>
	<div class = "permission-settings">

		<div class = "row">
			<div class = "col-md-12">
				<el-tabs tab-position="top" v-model="activeTab"  type = "border-card" @tab-click="handleClick">

					<el-tab-pane label="Super Admin" name="superadmin" lazy>
						<function-permission 
						:data-levels = "dataLevels"
						:data-permission = "superAdminList">
						</function-permission>
					</el-tab-pane>

					<el-tab-pane label="Admin" name="admin" lazy>
						<function-permission 
						:data-levels = "dataLevels"
						:data-permission = "adminList">
						</function-permission>
					</el-tab-pane>

					<el-tab-pane label="Manager" name="manager" lazy>
						<function-permission 
						:data-levels = "dataLevels"
						:data-permission = "managerList">
						</function-permission>
					</el-tab-pane>

					<el-tab-pane label="Normal" name="normal" lazy>
						<function-permission 
						:data-levels = "dataLevels"
						:data-permission = "normalList">
						</function-permission>
					</el-tab-pane>

					<!-- <el-tab-pane label="Limited" name="limited" lazy>
						<function-permission 
						:data-levels = "dataLevels"
						:data-permission = "limitedList">
						</function-permission>
					</el-tab-pane> -->
				</el-tabs>

				<!-- <ul class = "nav nav-tabs">
					<li class="active"><a href="#superadmin" data-toggle="tab">Super Admin</a></li>
					<li><a href="#admin" data-toggle="tab">Admin</a></li>
					<li><a href="#manager" data-toggle="tab">Manager</a></li>
					<li><a href="#normal" data-toggle="tab">Normal</a></li>
					<li><a href="#limited" data-toggle="tab">Limited</a></li>
				</ul> -->
			</div>
		</div>
	</div>
</template>

<script>
	import FunctionPermission from './FunctionPermission'

export default {
	props: ['dataLevels', 'dataAccessList', 'canEdit'],
	components: {FunctionPermission},
	data: function () {
		return{
			superAdminList 	: this.dataAccessList.superAdmin,
			adminList 		: this.dataAccessList.admin,
			managerList 	: this.dataAccessList.manager,
			normalList 		: this.dataAccessList.normal,
			limitedList 	: this.dataAccessList.limited,
			activeTab 		: 'superadmin'
		}
	},
	methods:{
		handleClick(){
			console.log("Click")
		}
	}
}
</script>