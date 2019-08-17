<template>
	<div class = "function-permission">
		<el-table
			border
			v-loading="loading"
			:data="functionPermissionData"
			fit>
			<el-table-column
				label="Function"
				prop = "function.description">
			</el-table-column>

			<el-table-column
				label="View"
				prop = "_view">
				<template slot-scope="scope">
					<span v-html = "getAccess(scope.row._view)"></span>
				</template>
			</el-table-column>

			<el-table-column
				label="Add"
				prop = "_add">
				<template slot-scope="scope">
					<span v-html = "getAccess(scope.row._add)"></span>
				</template>
			</el-table-column>

			<el-table-column
				label="Edit"
				prop = "_edit">
				<template slot-scope="scope">
					<span v-html = "getAccess(scope.row._edit)"></span>
				</template>
			</el-table-column>

			<el-table-column
				label="Delete"
				prop = "_delete">
				<template slot-scope="scope">
					<span v-html = "getAccess(scope.row._delete)"></span>
				</template>
			</el-table-column>
		</el-table>
	</div>
</template>

<script>
export default {
	props: ['dataLevels', 'dataPermission'],
	data: function () {
		return{
			functionPermissionData 	: this.dataPermission,
			loading : false
		}
	},
	methods:{
		getAccess(access){
			if(Number(access) == 1){
				return "<i class = 'fa fa-check color-green'></i>";
			}
			else{
				return "<i class = 'fa fa-remove color-red'></i>";
			}
		}
	}
}
</script>
<style lang="scss">
	.color-red{
		color: #ec1a1a;
	}

	.color-green{
		color: #039003;
	}
</style>