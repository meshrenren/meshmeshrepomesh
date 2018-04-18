<template>
<div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Client Data</h3>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
            <form class="form-horizontal">
              <div class="box-body">
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Customer ID</label>

                  <div class="col-sm-8">
                    <input type="email" class="form-control" id="inputEmail3" placeholder="Email" readOnly>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputPassword3" class="col-sm-2 control-label">Name</label>

                  <div class="col-sm-8">
                    <input type="text" class="form-control" id="inputPassword3" placeholder="Name" readOnly>
                  </div>

                  <el-button type="primary"  @click="dialogVisible = true" round>Find Member</el-button>
                </div>
          {{alien}}
              </div>
          

<el-dialog title="Find Member" :visible.sync="dialogVisible" width="45%">
  <search-member v-on:client-shet=lakosabot>

  </search-member>
  <span slot="footer" class="dialog-footer">
    <el-button @click="dialogVisible = false">Cancel</el-button>
    <el-button type="primary" @click="dialogVisible = false">Confirm</el-button>
  </span>
</el-dialog>
             
              <!-- /.box-body -->
          
              <!-- /.box-footer -->
            </form>
     </div>
</template>

<style lang="scss">
  	@import '../../assets/member.scss';
  	@import '~noty/src/noty.scss';
    @import '~element-ui/lib/theme-chalk/index.css';

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
    props: ['Name'],
    components: {
      SearchMember
    },
    data: function () {


      return {
               client: 'harold calioa',
               alien: 'alients',
               dialogVisible: false
             }
    },
    methods: {
     lakosabot: function(value)
     {
      alien = value;
     }
    }
  
}
</script>
