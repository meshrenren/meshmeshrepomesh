<template>
	<div class = "member-loan">
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