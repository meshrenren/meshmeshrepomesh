import Vue from 'vue'

import MemberForm from './components/Member/MemberForm.vue'
Vue.component('member-form', MemberForm)

import MemberView from './components/Member/MemberView.vue'
Vue.component('member-view', MemberView)

import SearchMember from './components/General/SearchMember.vue'
Vue.component('search-member', SearchMember)

import SearchCurrentLoans from './components/General/SearchCurrentLoans.vue'
Vue.component('search-current-loans', SearchCurrentLoans)

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

import TimeDepositCalculation from './components/TimeDeposit/TimeDepositCalculation.vue'
Vue.component('time-deposit-calculation', TimeDepositCalculation)

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

import LoanEvaluationForm from './components/Loan/LoanEvaluationForm.vue'
Vue.component('loan-evaluation-form', LoanEvaluationForm)

import LoanClosure from './components/Loan/LoanClosure.vue'
Vue.component('loan-closure', LoanClosure)

/* --- END --- */


/* General Voucher */
/* --- START --- */

import VoucherCreate from './components/Voucher/VoucherCreate.vue'
Vue.component('voucher-create', VoucherCreate)

import VoucherView from './components/Voucher/VoucherView.vue'
Vue.component('voucher-view', VoucherView)

import VoucherViewForm from './components/Voucher/VoucherViewForm.vue'
Vue.component('voucher-view-form', VoucherViewForm)

import EnterVoucher from './components/EnterVoucher.vue'
Vue.component('enter-voucher', EnterVoucher)

/* --- END --- */


/* Payment Record */
/* --- START --- */

import PaymentRecord from './components/Payment/PaymentRecord.vue'
Vue.component('payment-record', PaymentRecord)

import PaymentPayroll from './components/Payment/PaymentPayroll.vue'
Vue.component('payment-payroll', PaymentPayroll)

import PaymentList from './components/Payment/PaymentList.vue'
Vue.component('payment-list', PaymentList)

import PaymentRecordList from './components/Payment/PaymentRecordList.vue'
Vue.component('payment-record-list', PaymentRecordList)

import PaymentImport from './components/Payment/PaymentImport.vue'
Vue.component('payment-import', PaymentImport)

import PaymentCancellation from './components/Payment/PaymentCancellation.vue'
Vue.component('payment-cancellation', PaymentCancellation)

/* --- END --- */


/* Settings */
/* --- START --- */

import PermissionSettings from './components/Settings/PermissionSettings.vue'
Vue.component('permission-settings', PermissionSettings)

import ProductSettings from './components/Settings/ProductSettings.vue'
Vue.component('product-settings', ProductSettings)

/* --- END --- */


/* Gloabl */
/* --- START --- */

import DialogModal from './components/General/DialogModal.vue'
Vue.component('dialog-modal', DialogModal)

import PrintHeader from './components/General/PrintHeader.vue'
Vue.component('print-header', PrintHeader)


/* --- END --- */