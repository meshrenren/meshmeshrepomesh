<template>
	<div class = "member-share">
		Share Accounts
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