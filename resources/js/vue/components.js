import Vue from 'vue'

import MemberForm from './components/Member/MemberForm.vue'
Vue.component('member-form', MemberForm)

import MemberView from './components/Member/MemberView.vue'
Vue.component('member-view', MemberView)

import ShareAccountForm from './components/Shareaccounts/ShareAccountForm.vue'
Vue.component('share-account-form', ShareAccountForm)

import SavingsForm from './components/Savings/SavingsForm.vue'
Vue.component('savings-form', SavingsForm)


import SearchMember from './components/General/SearchMember.vue'
Vue.component('search-member', SearchMember)

import PermissionSettings from './components/Settings/PermissionSettings.vue'
Vue.component('permission-settings', PermissionSettings)