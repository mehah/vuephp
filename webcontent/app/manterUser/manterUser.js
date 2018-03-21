this.el = "#app";

this.data.modal = {
	onCloseModal : function(data) {
		if (data) {
			if (this.user.id) {
				this.goback();
			} else {
				this.user = JSON.parse(JSON.stringify($data.user));
			}
		}
	}
};