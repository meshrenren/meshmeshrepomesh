<template>
    <div>
         <div class="box box-default">
                <div class="box-header with-border"><h3 class="box-title">General Voucher Summary View</h3></div>

                <div class="box-body">
                    <el-form :model="voucherDetails" ref="voucherDetails" :rules = "formRule" label-width="160px" class="demo-dynamic" :label-position="labelPosition" @submit.native.prevent>
                        <el-row :gutter="20">
                            <el-col :span="16">
                                <el-form-item label="Name" prop = "name">
                                    <el-input type="text" v-model="voucherDetails.name" ref="name"></el-input>
                                </el-form-item>

                                <el-row :gutter="20">
                                    <el-col :span="12">
                                        <el-form-item label="Period From" prop = "period_from" label-width="200px">
									  		<el-date-picker v-model="voucherDetails.period_from" type="date" placeholder="From" ref="period_from"> </el-date-picker>
									  	</el-form-item>
                                    </el-col>
                                    <el-col :span="12">
                                        <el-form-item label="Period To" prop = "period_to" label-width="200px">
									  		<el-date-picker v-model="voucherDetails.period_to" type="date" placeholder="To" ref="period_to"> </el-date-picker>
									  	</el-form-item>
                                    </el-col>

                                </el-row>
                            </el-col>

                            <el-col :span="8">
                                <el-form-item label="Particulars" prop = "particular" label-width="200px">
									<el-select v-model="voucherDetails.particular" filterable placeholder="Select Type" ref = "particular"  :default-first-option = "true" >
										<el-option
										    v-for="item in particulars"
											      :key="item.id"
											      :label="item.name"
											      :value="item.id">
										</el-option>
									</el-select>
								</el-form-item>

                                <el-row :gutter="20">
                                    <el-col :span="12">
                                        Total Debit: {{$nf.formatNumber(totalDebit, 2) }}
                                    </el-col>
                                    <el-col :span="12">
                                        Total Credit: {{$nf.formatNumber(totalCredit, 2)}}
                                    </el-col>

                                </el-row>


                            </el-col>
                        </el-row>
                         <el-row :gutter="20">
                            <el-col :span="10">
                                <div>
                                    <el-button type = "primary"  ref = "printbutton" >Print</el-button>  
                                    <el-button type = "primary"  ref = "verifybutton" @click="generateReport()" >Generate Report</el-button> 
                                </div>
                            </el-col>
                         </el-row>
                    </el-form>


                    <el-row :gutter="20" style="margin-top:10px;">
                        <el-col :span="18" >
                                <div>
                                    <el-table 
                                        :data="journalEntries"
                                        style="width: 100%" 
                                        height="450px" stripe border 
                                        v-loading = "loadingTable">

                                            <el-table-column label="Date Transacted">
                                                <template slot-scope="scope">
                                                    <span style="margin-left: 10px">{{ scope.row.voucher.date_transact }}</span>
                                                </template>
                                            </el-table-column>


                                            <el-table-column label="GV No.">
                                                <template slot-scope="scope">
                                                    <span style="margin-left: 10px">{{ scope.row.gv_num }}</span>
                                                </template>
                                            </el-table-column>


                                            <el-table-column label="Description">
                                                <template slot-scope="scope">
                                                    <span style="margin-left: 10px">{{ scope.row.particular.name }}</span>
                                                </template>
                                            </el-table-column>


                                            <el-table-column label="Debit">
                                                <template slot-scope="scope">
                                                    <span style="margin-left: 10px">{{ scope.row.debit }}</span>
                                                </template>
                                            </el-table-column>

                                            <el-table-column label="Credit">
                                                <template slot-scope="scope">
                                                    <span style="margin-left: 10px">{{ scope.row.credit }}</span>
                                                </template>
                                            </el-table-column>


                                    </el-table>
                                </div>
                        </el-col>
                    </el-row>
                </div>
         </div>
    
    </div>
</template>


<script>
import sumBy from 'lodash/sumBy' 

export default {
    
    data() {


        return {
            labelPosition: 'right',
            voucherDetails: {
                name: '',
                particular: '',
                period_from: '',
                period_to: '',
                total_debit: 0,
                total_credit: 0

            },
            particulars: [],
            formRule: [],

            journalEntries: [],
            loadingTable: false

        }
    },


    computed: {
        totalDebit: function() {
            return sumBy(this.journalEntries, o => {
                    return Number(o.debit);
            });
        },


        totalCredit: function() {
            return sumBy(this.journalEntries, o => {
                    return Number(o.credit);
            });
        }

    },



    created()
    {
        this.formRule = {
  			period_from : [{ required: true, message: 'Period from be blank.', trigger: 'blur' }],
  			period_to : [{ required: true, message: 'Period to cannot be blank.', trigger: 'blur' }],
  			particular : [{ required: true, message: 'Particulars cannot be blank.', trigger: 'changed' }],
        }
        
        this.getParticulars();
    },

    methods: {

        generateReport() {
            this.$refs.voucherDetails.validate((valid) => {
                if(valid)
                {
                    let params = {
                        name: this.voucherDetails.name,
                        particular_id : this.voucherDetails.particular,
                        date_from : this.voucherDetails.period_from,
                        date_to : this.voucherDetails.period_to,
                    }

                    let vm = this;
                    vm.loadingTable = true;
                    this.$API.Voucher.getAllVoucherSummaryPerParticulars(params).then(
                        result => {
                                let res = result.data;
                          //  console.log("woi woi woi", result.data);

                          
                            vm.journalEntries = result.data;
                            

                            vm.loadingTable = false;

                        }).catch(err=> {
                                vm.loadingTable = false;
                                console.log("errx", err)
                        })
                   
                    


                }
            })
        },

        getParticulars()
        {
            let vm = this;
            this.$API.Voucher.getParticulars().then(
                    result=>{
                       
                        let res = result.data;
                       if(res.length)
                       {
                           vm.particulars = res;

                       }


                    }
            ).catch(err=> {

            })
            
        }

    }
}
</script>