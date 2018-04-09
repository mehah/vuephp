App("#app");

this.methods.entrar = function() {
	this.$entrar(this.login, function(data) {
		if (this.logged) {
			this.redirect('home');
		} else if(data.msg){
			Modal.message(data.msg[0]);
		} else {
			Modal.message('Usuário ou senha invalido(s).');
		}
	});
};