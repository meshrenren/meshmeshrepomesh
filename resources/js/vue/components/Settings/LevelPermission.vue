<template>
	<div class = "level-permission">

		<div class = "row">
			<div class = "col-md-12">
				<ul class = "nav nav-tabs">
					<li class="active"><a href="#superadmin" data-toggle="tab">Super Admin</a></li>
					<li><a href="#admin" data-toggle="tab">Admin</a></li>
					<li><a href="#manager" data-toggle="tab">Manager</a></li>
					<li><a href="#normal" data-toggle="tab">Normal</a></li>
					<li><a href="#limited" data-toggle="tab">Limited</a></li>
				</ul>
				<div class = "tab-content">
					<div class = "active tab-pane" id = "general">
						<table class = "table table-bordered table-hover dataTable">
							<thead>
								<tr>
									<th>Function</th>
									<th>View</th>
									<th>Add</th>
									<th>Edit</th>
									<th>Delete</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="(item, index) in superAdminList" :key = "index">
									<td>
										<span>{{ item.function.description }}</span>
									</td>
									<td>
										<span v-html = "getAccess(item._view)"></span>
									</td>
									<td>
										<span v-html = "getAccess(item._add)"></span>
									</td>
									<td>
										<span v-html = "getAccess(item._edit)"></span>
									</td>
									<td>
										<span v-html = "getAccess(item._delete)"></span>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	props: ['dataLevels', 'dataSuperAdmin', 'dataAdmin', 'dataManager', 'dataNormal', 'dataLimited', 'baseUrl'],
	data: function () {
		return{
			superAdminList 	: this.dataSuperAdmin,
			adminList 		: this.dataAdmin,
			managerList 	: this.dataManager,
			normalList 		: this.dataNormal,
			limitedList 	: this.dataLimited,
		}
	},
	methods:{
		getAccess(access){
			if(access){
				return "<i class = 'fa fa-check'></i>";
			}
			else{
				return "<i class = 'fa fa-remove'></i>";
			}
		}
	}
}
</script>