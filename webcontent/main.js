var Modal;
document.addEventListener("DOMContentLoaded", function() {
	Modal = new Vue({
		el : '#modal',
		components : {
			'modal' : {
				template : '#modal-template'
			}
		},
		data : {
			openned : false,
			isConfirm : false,
			msg : null,
			closeButtonTxt : 'close',
			onClose : null,
			onConfirm : null
		},
		methods : {
			message : function(msg, onClose) {
				this.openned = true;
				this.isConfirm = false;
				this.closeButtonTxt = 'OK';
				this.msg = msg;
				this.onClose = onClose || null;
			},
			messageConfirm : function(msg, onConfirm, onClose) {
				this.openned = true;
				this.isConfirm = true;
				this.closeButtonTxt = 'NÃO';
				this.msg = msg;
				this.onConfirm = onConfirm || null;
				this.onClose = onClose || null;
			},
			close : function() {
				this.openned = false;
				if (this.onClose) {
					this.onClose.call(this);
				}
			},
			confirm : function() {
				this.openned = false;
				if (this.onConfirm) {
					this.onConfirm.call(this);
				}
			}
		}
	});
});

Vue.mixin({
	methods : {
		logout : function() {
			var currentApp = this.$children[0];
			Modal.messageConfirm("Tem certeza que deseja sair?", function() {
				currentApp.$logout(function() {
					this.logged = false;
					this.redirect('home');
				});
			});
		}
	}
});