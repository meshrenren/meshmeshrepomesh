<template>
<div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Add Share Account</h3>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
<!-- start of body -->
      <el-form  label-width="120px" :model="shareaccount" ref="shareaccount">
        <el-form-item label="Customer ID">
          <el-input v-model="shareaccount.fk_memid" ></el-input>
        </el-form-item>
        <el-form-item label="Name">
          <el-input v-model="fullname" ></el-input>
        </el-form-item>

        <el-form-item>
           <el-button type="primary"  @click="showModal = true" round>Find Member</el-button>
        </el-form-item>
        

        <el-form-item label="Share Product" prop="shareProduct" ref="shareProduct">
          <el-select v-model = "shareaccount.fk_share_product" prop="shareProduct"  placeholder="Please Select Share Product">
            <el-option
              v-for="product in shares" :key="product.id" :label="product.name" :value="product.id">
            </el-option>
           
          </el-select>
        </el-form-item>
        
        <el-form-item label="No. Of Shares">
          <el-input v-model="shareaccount.no_of_shares" :value="shareaccount.no_of_shares"></el-input>
        </el-form-item>

        
        <el-form-item label="" prop="shareProduct" ref="shareProduct">
   
            <el-checkbox @change="toggleDeposit()" v-model="shareaccount.isWithDeposit" prop="shareProduct" label="Is With Deposit" name="type"></el-checkbox>
     
        </el-form-item>
        
        <el-form-item label="Amount" v-if="isWithDep">
          <el-input v-model="shareaccount.Deposit" type="number"></el-input>
        </el-form-item>
        
        <el-form-item>
            <el-button @click="onSubmit()" type="primary" >Creates</el-button>
            <el-button>Cancel</el-button>
        </el-form-item>

      </el-form>
<!-- end of body -->

  <search-member :base-url="baseUrl" v-if = "showModal" @select="populateField" @close = "showModal = false" >

  </search-member>
 
             
              <!-- /.box-body -->
          
              <!-- /.box-footer -->
           
     </div>
</template>

<style lang="scss">
  	@import '../../assets/member.scss';
  	@import '~noty/src/noty.scss';


</style>


<script>
window.noty = require('noty');
    import axios from 'axios'
    import Noty from 'noty'
    import Multiselect from 'vue-multiselect'
    import DatePicker from 'vue2-datepicker'
    import VueTimepicker from 'vue2-timepicker'
    import {Tabs, Tab} from 'vue-tabs-component'
    import VueTabs from 'vue-nav-tabs'
    import VTab from 'vue-nav-tabs'
    import DataTable from 'vue-materialize-datatable'
    import SearchMember from '../General/SearchMember.vue'
    




export default {
    props: ['baseUrl', 'shareProduct', 'shareAccountDetails'],
    components: {
      SearchMember
    },
    data: function () {


      return {
               client: 'harold calioa',
               alien: 'alients',
               shares: this.shareProduct,
               shareaccount: this.shareAccountDetails, //this is our form model
               id: "",
               fullname: "",
               dialogVisible: false,
               showModal:false,
               isWithDep: false
             }
    },
    methods: {

    populateField(row){
      console.log("populateFields",row)
      this.id = row.id
      this.fullname = row.fullname
      this.shareaccount.fk_memid = row.id;
    },

     toggleDeposit(){
        console.log(this.shareaccount.isWithDeposit);
        this.shareaccount.Deposit = 0;
        this.isWithDep = this.shareaccount.isWithDeposit;
        
     },


     onSubmit()
     {
       //first get the form data
       let dataSubmitted = new FormData();

       dataSubmitted.set('shareaccount', JSON.stringify(this.shareaccount));

       axios.post(this.baseUrl+'/shareaccount/createaccount', dataSubmitted).then((result)=>{
          let res  = result.data;
          console.log(res);
       });


     }



    }
  
}
</script>
