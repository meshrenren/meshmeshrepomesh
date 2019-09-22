import Vue from 'vue'

import MemberForm from './components/Member/MemberForm.vue'
Vue.component('member-form', MemberForm)

import MemberView from './components/Member/MemberView.vue'
Vue.component('member-view', MemberView)

import SearchMember from './components/General/SearchMember.vue'
Vue.component('search-member', SearchMember)

import BeginningOfDay from './components/General/BeginningOfDay.vue'
Vue.component('beginning-of-day', BeginningOfDay)



/* Share Account */
/* --- START --- */

import ShareAccountForm from './components/Shareaccounts/ShareAccountForm.vue'
Vue.component('share-account-form', ShareAccountForm)

import ShareDeposit from './components/Shareaccounts/ShareDeposit.vue'
Vue.component('share-deposit', ShareDeposit)

import ShareList from './components/Shareaccounts/ShareList.vue'
Vue.component('share-list', ShareList)

/* --- END --- */


/* Savings Account */
/* --- START --- */

import SavingsDepositForm from './components/Savings/SavingsDepositForm.vue'
Vue.component('savings-deposit-form', SavingsDepositForm)

import SavingsWithdrawForm from './components/Savings/SavingsWithdrawForm.vue'
Vue.component('savings-withdraw-form', SavingsWithdrawForm)

import SavingsAccountCreate from './components/Savings/SavingsAccountCreate.vue'
Vue.component('savings-account-create', SavingsAccountCreate)

import SavingsAccountList from './components/Savings/SavingsAccountList.vue'
Vue.component('savings-account-list', SavingsAccountList)

/* --- END --- */


/* Time Deposit Account */
/* --- START --- */
import TimeDepositCreate from './components/TimeDeposit/TimeDepositCreate.vue'
Vue.component('time-deposit-create', TimeDepositCreate)

import TimeDepositList from './components/TimeDeposit/TimeDepositList.vue'
Vue.component('time-deposit-list', TimeDepositList)

/* --- END --- */


/* Loan Account */
/* --- START --- */

import LoanEvaluation from './components/Loan/LoanEvaluation.vue'
Vue.component('loan-evaluation', LoanEvaluation)

import LoanForApproval from './components/Loan/LoanForApproval.vue'
Vue.component('loan-for-approval', LoanForApproval)

import LoanVoucher from './components/Loan/LoanVoucher.vue'
Vue.component('loan-voucher', LoanVoucher)

import LoanList from './components/Loan/LoanList.vue'
Vue.component('loan-list', LoanList)

/* --- END --- */


/* General Voucher */
/* --- START --- */

import VoucherCreate from './components/Voucher/VoucherCreate.vue'
Vue.component('voucher-create', VoucherCreate)

import VoucherView from './components/Voucher/VoucherView.vue'
Vue.component('voucher-view', VoucherView)

/* --- END --- */


/* Payment Record */
/* --- START --- */

import PaymentRecord from './components/Payment/PaymentRecord.vue'
Vue.component('payment-record', PaymentRecord)

import PaymentPayroll from './components/Payment/PaymentPayroll.vue'
Vue.component('payment-payroll', PaymentPayroll)

import PaymentList from './components/Payment/PaymentList.vue'
Vue.component('payment-list', PaymentList)

/* --- END --- */


/* Settings */
/* --- START --- */

import PermissionSettings from './components/Settings/PermissionSettings.vue'
Vue.component('permission-settings', PermissionSettings)

import ProductSettings from './components/Settings/ProductSettings.vue'
Vue.component('product-settings', ProductSettings)

/* --- END --- */