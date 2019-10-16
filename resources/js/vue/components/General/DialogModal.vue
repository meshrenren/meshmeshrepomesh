<template>
	<el-dialog @close="closeModal" :visible.sync="dialogVisible" :width = "width" top = "20px" >
		<span slot="title" class="el-dialog__title" >{{ titleHeader }}</span>
		<slot></slot>
	</el-dialog>
</template>

<script>
import {dialogComponent} from '../../mixins/dialogComponent.js'

export default {
    props: {
    	titleHeader :{
    		type : String, 
    		deault : ""
    	},
    	width : {
    		type : String, 
    		deault : "40%"
    	}
    },
    data(){
    	return {
			dialogVisible		: true,
    	}
    },
    mixins: [dialogComponent],
}
</script>

