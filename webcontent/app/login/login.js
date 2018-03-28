App("#app");

this.methods.entrar = function() {
	this.$entrar(this.login, function(data) {
		if(this.logged) {
			this.redirect('home');
		} else {
			Modal.message(this.msg);
		}
	});
};