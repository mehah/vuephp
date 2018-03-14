// register modal component
Vue.component('modal', {
  template: '#modal-template'
});

VUE_GLOBAL.data.modal = {
		openned: false,
		confirm: false,
		message: null,
		onCloseModal: null,
}

VUE_GLOBAL.methods.closeModal = function() {
	this.modal.openned = false;
	if(this.modal.onCloseModal) {
		this.modal.onCloseModal.call(this);
	}
};