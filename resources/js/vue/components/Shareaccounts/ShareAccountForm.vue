<template>
<div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Add Share Account</h3>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
            <form class="form-horizontal">
              <div class="box-body">
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Customer ID</label>

                  <div class="col-sm-8">
                    <input type="text" v-model="id" class="form-control" id="inputEmail3" placeholder="ID" readOnly>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputPassword3" class="col-sm-2 control-label">Name</label>

                  <div class="col-sm-8">
                    <input type="text" v-model="fullname" class="form-control" id="inputPassword3" placeholder="Name" readOnly>
                  </div>

                  <el-button type="primary"  @click="showModal = true" round>Find Member</el-button>
                </div>

              </div>
          
<!-- start of body -->
      <el-form  label-width="120px" :model="shareaccount" ref="shareaccount">
        <el-form-item label="Share Product" prop="shareProduct" ref="shareProduct">
          <el-select v-model = "shareaccount.fk_share_product" prop="shareProduct"  placeholder="Please Select Share Product">
            <el-option
              v-for="product in shares" :key="product.id" :label="product.name" :value="product.id">
            </el-option>
            <el-option label="Zone two" value="beijing"></el-option>
          </el-select>
        </el-form-item>
        
        <el-form-item label="No. Of Shares">
          <el-input :v-model="shareaccount.no_of_shares" :value="shareaccount.no_of_shares"></el-input>
        </el-form-item>

        
        <el-form-item label="Activity time">
          <el-col :span="11">
            <el-date-picker type="date" placeholder="Pick a date"  style="width: 100%;"></el-date-picker>
          </el-col>
          <el-col class="line" :span="2">-</el-col>
          <el-col :span="11">
            <el-time-picker type="fixed-time" placeholder="Pick a time"  style="width: 100%;"></el-time-picker>
          </el-col>
        </el-form-item>
        <el-form-item label="Instant delivery">
          <el-switch ></el-switch>
        </el-form-item>
        <el-form-item label="Activity type">
          <el-checkbox-group >
            <el-checkbox label="Online activities" name="type"></el-checkbox>
            <el-checkbox label="Promotion activities" name="type"></el-checkbox>
            <el-checkbox label="Offline activities" name="type"></el-checkbox>
            <el-checkbox label="Simple brand exposure" name="type"></el-checkbox>
          </el-checkbox-group>
        </el-form-item>
        <el-form-item label="Resources">
          <el-radio-group >
            <el-radio label="Sponsor"></el-radio>
            <el-radio label="Venue"></el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="Activity form">
          <el-input type="textarea"></el-input>
        </el-form-item>
        <el-form-item>
          <el-button type="primary">Create</el-button>
          <el-button>Cancel</el-button>
        </el-form-item>
      </el-form>
<!-- end of body -->

  <search-member :base-url="baseUrl" v-if = "showModal" @select="populateField" @close = "showModal = false" >

  </search-member>
 
             
              <!-- /.box-body -->
          
              <!-- /.box-footer -->
            </form>
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
               shareaccount: this.shareAccountDetails,
               id: "",
               fullname: "",
               dialogVisible: false,
               showModal:false
             }
    },
    methods: {

    populateField(row){
      console.log("populateFields",row)
      this.id = row.id
      this.fullname = row.fullname
    },

     lakosabot: function(value)
     {
      alien = value;
     }
    }
  
}
</script>
