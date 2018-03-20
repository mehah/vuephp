// register modal component
Vue.component('modal', {
  template: '#modal-template'
});

VUE_GLOBAL.data.modal = {
		openned: false,
		confirm: false,
		message: null,
		closeButtonTxt: 'close',
		/*onCloseModal: null,
		onConfirmModal: null*/
}

VUE_GLOBAL.methods.showModal = function(message, data) {
	this.modal.openned = true;
	this.modal.confirm = false;
	this.modal.closeButtonTxt = 'OK';
	this.modal.message = message;
	this.modal.data = data || null;
};

VUE_GLOBAL.methods.showCofirmModal = function(message, onConfirm, data) {
	this.showModal(message);
	this.modal.confirm = true;
	this.modal.closeButtonTxt = 'NÃO';
	this.modal.onConfirmModal = onConfirm || null;
	this.modal.data = data || null;
};

VUE_GLOBAL.methods.closeModal = function() {
	this.modal.openned = false;
	if(this.modal.onCloseModal) {
		this.modal.onCloseModal.call(this, this.modal.data);
	}
};

VUE_GLOBAL.methods.confirmModal = function() {
	this.modal.openned = false;
	if(this.modal.onConfirmModal) {
		this.modal.onConfirmModal.call(this, this.modal.data);
	}
};

VUE_GLOBAL.methods.logout = function() {
	this.showCofirmModal("Tem certeza que deseja sair?", function() {
		this.request('login/sair', function(data) {
			this.redirect('home');
		});
	});
};

