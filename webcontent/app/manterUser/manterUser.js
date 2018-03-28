App("#app");

this.data.modal = {
	onCloseModal : function(data) {
		if (data) {
			if (currentApp.user.id) {
				currentApp.goback();
			} else {
				Vue.util.clear(currentApp.user);
			}
		}
	}
};

this.methods.manter = function() {
	var currentApp = this;
	this[this.user.id ? '$alterar' : '$inserir'](this.user, function(data) {
		Modal.message(data[1], function() {
			if (data[0]) {
				if (currentApp.user.id) {
					currentApp.goback();
				} else {
					Vue.util.clear(currentApp.user);
				}
			}
		});
	});
};