export const dialogComponent = {
  data() {
    return {
      showDialog: false,
    }
  },
  methods: {
    closeModal() {
      this.showDialog = false
      setTimeout(() => {
        this.$emit('close')
      }, 100)
    },
  },
  mounted() {
    this.showDialog = true;
  }
}
