<template>
	<div class = "member-savings">
		Saving Account
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