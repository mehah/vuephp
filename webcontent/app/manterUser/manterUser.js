this.el = "#content";

this.data.modal = {onCloseModal: function() {
	if(this.user.id) {
		this.goback();
	} else {
		this.user = {};
	}
}};