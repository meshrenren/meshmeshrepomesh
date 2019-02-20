import Vue from 'vue'

import MemberForm from './components/Member/MemberForm.vue'
Vue.component('member-form', MemberForm)

import MemberView from './components/Member/MemberView.vue'
Vue.component('member-view', MemberView)

import ShareAccountForm from './components/Shareaccounts/ShareAccountForm.vue'
Vue.component('share-account-form', ShareAccountForm)


import SearchMember from './components/General/SearchMember.vue'
Vue.component('search-member', SearchMember)

import PermissionSettings from './components/Settings/PermissionSettings.vue'
Vue.component('permission-settings', PermissionSettings)

//Savings
import SavingsDepositForm from './components/Savings/SavingsDepositForm.vue'
Vue.component('savings-deposit-form', SavingsDepositForm)

import SavingsWithdrawForm from './components/Savings/SavingsWithdrawForm.vue'
Vue.component('savings-withdraw-form', SavingsWithdrawForm)

import SavingsAccountCreate from './components/Savings/SavingsAccountCreate.vue'
Vue.component('savings-account-create', SavingsAccountCreate)

//Time Deposit
import TimeDepositCreate from './components/TimeDeposit/TimeDepositCreate.vue'
Vue.component('time-deposit-create', TimeDepositCreate)
import TimeDepositList from './components/TimeDeposit/TimeDepositList.vue'
Vue.component('time-deposit-list', TimeDepositList)

//Loan
import LoanEvaluation from './components/Loan/LoanEvaluation.vue'
Vue.component('loan-evaluation', LoanEvaluation)

//General Voucher
import VoucherCreate from './components/Voucher/VoucherCreate.vue'
Vue.component('voucher-create', VoucherCreate)
import VoucherView from './components/Voucher/VoucherView.vue'
Vue.component('voucher-view', VoucherView)

//Payment Record
import PaymentRecord from './components/Payment/PaymentRecord.vue'
Vue.component('payment-record', PaymentRecord)
import PaymentPayroll from './components/Payment/PaymentPayroll.vue'
Vue.component('payment-payroll', PaymentPayroll)