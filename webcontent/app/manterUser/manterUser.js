this.el = "#content";

this.data.modal = {
	onCloseModal : function(data) {
		if (data) {
			if (this.user.id) {
				this.goback();
			} else {
				this.user = $data.user;
			}
		}
	}
};