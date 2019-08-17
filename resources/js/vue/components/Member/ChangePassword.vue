<template>
    <el-dialog title="Change Password" :visible.sync="dialogVisible"  width="45%" top = "20px" @close="closeModal">      
        <el-form :model="formModel" :rules="ruleForm" ref="formModel" label-position = "top" @submit.native.prevent="savePassword">
            <el-form-item label="Current Password" prop = "oldPassword">
                <el-input type = "password" v-model="formModel.oldPassword" ></el-input>
            </el-form-item>
            <el-form-item label="New Password" prop = "newPassword">
                <el-input type = "password" v-model="formModel.newPassword" ></el-input>
            </el-form-item>
            <el-form-item label="Confirm Password" prop = "confirmPassword">
                <el-input type = "password" v-model="formModel.confirmPassword" ></el-input>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button @click="dialogVisible = false">Cancel</el-button>
            <el-button type="primary" @click="savePassword()">Save</el-button>
        </div>
    </el-dialog>
</template>


<style lang="scss">
  	@import '../../assets/member.scss';
  	@import '~noty/src/noty.scss';

</style>


<script>
window.noty = require('noty')
    import Noty from 'noty'
    import {dialogComponent} from '../../mixins/dialogComponent.js'


export default {
    props: ["showModal", "memberId"],
    data: function () {
        let formModel = {oldPassword : null, newPassword : null, confirmPassword : null}
        return {
            dialogVisible   : this.showModal,
            formModel       : formModel,
            formLoading     : false,
            ruleForm        : []
        }
    },
    created(){
        var validatePassword = (rule, value, callback) => {
            if (value === '') {
                callback(new Error('Please confirm new password.'));
            } else if (value !== this.formModel.newPassword) {
                callback(new Error('Password not match.'));
            } else {
                callback();
            }
        };

        var validateNewPassword = (rule, value, callback) => {
            if (value === '') {
                callback(new Error('New Password is required.'));
            } else if (value.length < 7) {
                callback(new Error('Please enter up to 7 characters.'));
            } else {
                callback();
            }
        };
        this.ruleForm = {
            oldPassword : [{ required: true, message: 'Current Password is required.', trigger: 'blur' },],
            newPassword : [{ required: true, message: 'New Password is required.', trigger: 'blur' }, { validator: validateNewPassword, trigger: 'blur' },],
            confirmPassword : [{ required: true, message: 'Please confirm new password.', trigger: 'blur' }, { validator: validatePassword, trigger: 'blur' },],
        }
    },
    methods: {
        savePassword(){
            let vm = this   
            this.$refs.formModel.validate((valid) => {
                if (valid) {
                    this.$confirm('Are you sure you want to change your current password?', 'Warning', {
                        confirmButtonText: 'OK',
                        cancelButtonText: 'Cancel',
                        type: 'warning'
                    }).then(() => {
                        this.pageLoading = true
                        this.$API.Member.changePassword(this.formModel, this.memberId)
                        .then(result => {
                            var res = result.data
                            if(res.success){
                                location.reload()
                            }
                            else{
                                if(res.error == "ERROR_PASSWORD"){
                                    this.$message({
                                        showClose: true,
                                        message: 'Current password not match.',
                                        type: 'error'
                                    });
                                }
                                else{
                                    this.$message({
                                        showClose: true,
                                        message: 'Not successfully saved. Please try again.',
                                        type: 'error'
                                    })
                                }
                            }
                        })
                        .catch(err => {
                            console.log(err)
                        })
                        .then(_ => { 
                            this.pageLoading = false
                        })

                    }).catch(err => {
                        console.log(err)   
                    });
                }
            })
        }
    },

    mixins: [dialogComponent],
    watch: {
        showModal : function(val){     
            this.dialogVisible = val
        }
    }
  
}
</script>
