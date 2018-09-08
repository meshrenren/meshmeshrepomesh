<template>
	<div class = "member-timedeposit">
		Time Deposit
	</div>
</template>
<script>


export default {
	props: ['member', 'canEdit'],
	data: function () {
		return{
			memberData : this.member
		}
	},
}
</script>