<template>
    <el-dialog :title="rateTitle" :visible.sync="dialogVisible"  width="45%" @close="closeModal">      
        <el-table :data="productRate" style="width: 100%" height="400" stripe border>
            <el-table-column label="Minimum Amount">
                <template slot-scope="scope">
                    <span style="margin-left: 10px">{{ numberFormat(scope.row.min_amount) }}</span>
                </template>
            </el-table-column>
            <el-table-column label="Maxumin Amount">
                <template slot-scope="scope">
                    <span style="margin-left: 10px">{{ numberFormat(scope.row.max_amount) }}</span>
                </template>
            </el-table-column>
            <el-table-column label="Term (Days)">
                <template slot-scope="scope">
                    <span style="margin-left: 10px">{{ scope.row.day_from }}</span>
                </template>
            </el-table-column>
            <el-table-column label="Interest Rate">
                <template slot-scope="scope">
                    <span style="margin-left: 10px">{{ scope.row.interest_rate }}%</span>
                </template>
            </el-table-column>
        </el-table>
        <span slot="footer" class="dialog-footer">
            <el-button @click="dialogVisible = false">Cancel</el-button>
        </span>
    </el-dialog>
</template>


<script>
    import {dialogComponent} from '../../mixins/dialogComponent.js'
    import cloneDeep from 'lodash/cloneDeep'
    import sortBy from 'lodash/sortBy'


export default {
    props: ['productDetail'],
 
    data: function () {
        return {
            productRate     : [],
            tableData       : null,
            nameInput       : "",
            dialogVisible   : true,
            rateTitle       : this.productDetail.description + " Rate List"
        }
    },
    created(){
        let rate = cloneDeep(this.productDetail.ratetable)
        sortBy(rate, ret => {
          return ret.min_amount;
        });

        this.productRate = rate
    },
    methods:{
        numberFormat(num){
          return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }
    },
    mixins: [dialogComponent],
  
}
</script>
