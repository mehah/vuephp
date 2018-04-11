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