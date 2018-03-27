this.el = "#app";

this.data.modal = {
	onCloseModal : function(data) {
		if (data) {
			if (this.user.id) {
				this.goback();
			} else {
				Vue.util.clear(this.user);
			}
		}
	}
};