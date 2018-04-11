window.Modal = new Vue({
	el : '#modal',
	components : {
		'modal' : {
			template : '<div class="modal-mask"><div class="modal-wrapper"><div class="modal-container"><div class="modal-header"><slot name="header"></slot></div>'
					+ '<div class="modal-body"><slot name="body"></slot></div><div class="modal-footer"><slot name="footer"></slot></div></div></div></div>'
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