<template>

    <div>
       <div class="box box-default">
                <div class="box-header with-border"><h3 class="box-title">Payments Cancellation</h3></div>

                <div class="box-body">
                    <el-form :model="paymentDetails" ref="paymentForm" label-width="160px" :label-position="labelPosition">
                        <el-row :gutter="20">
                                <el-col :span="10">
                                        <el-form-item label="OR No.">
                                            <el-input type="text" v-model="paymentDetails.or_number"></el-input>
                                        </el-form-item>
                                </el-col>
                        </el-row>


                        <el-row :gutter="20">
                            <el-col :span="10">
                                <div>
                                     <el-button @click="cancelPayment" style="float:right; margin-right:20px;" type = "primary"  ref = "newLoan" >Cancel Payment</el-button>                                    
                                    <el-button @click="initializePayment" style="float:right; margin-right:20px;" type = "primary"  ref = "newLoan" >Find Payment</el-button>
                                </div>
                            </el-col>
                         </el-row>

                    </el-form>

                </div>
       </div>
    </div>

  
</template>

<script>
window.Noty = require('noty')
import Noty from 'noty'

export default {
    data() {
       
       return {
            labelPosition  : 'left',
            paymentDetails : {
                                or_number: null
                             },
            paymentList: []
       }
    },

    created() {
        //this.initializePayment();
    },
    
    methods: {

        initializePayment()
        {
            let vm = this;
            this.$API.Payment.getPaymentForCancellation(this.paymentDetails.or_number).then(result=>{
                //console.log('im it', result.data);
                let res = result.data;
                if(res.length == 0)
                {
   
               

                    new Noty({
                         theme: 'relax',
                         type: "error",
                         layout: 'topRight',
                         text: "No Payment Found.",
                         timeout: 3000

                    }).show();
                }

                else {
                    console.log(res)
                    vm.paymentList = res[0].paymentlist
                    alert('perfect');
                }

            })
            
        },


        cancelPayment()
        {
            let vm = this
    		if(this.paymentList.length>=1){
    			vm.$swal({
	                title: "POst Payment",
	                text: "Are you sure you want to save payment list? This can't be undone",
	                type: 'warning',
	                showCancelButton: true,
	                cancelButtonColor: '#d33',
	                confirmButtonText: 'Proceed',
	                focusConfirm: false,
	                focusCancel: true,
	                cancelButtonText: 'Cancel',
	                reverseButtons: true,
	                width: '400px',
	            }).then( result => {
	            	console.log('result', result)
	                if (result.value) {

			    		var winFeature = 'location=no,toolbar=no,menubar=no,scrollbars=yes,resizable=yes';
						window.open(this.$baseUrl + "/payment/process-payment-cancellation?ref_id="+vm.paymentDetails.or_number, 'null', winFeature);

						location.reload();
	                }
	            })

    			
    		}
    		else{
    			 new Noty({
                    theme: 'relax',
                    type: "error",
                    layout: 'topRight',
                    text: "No record to post.",
                    timeout: 3000
                }).show();
    		}

        }

    }
}
</script>

<style>

</style>
