this.methods.cadastrar = function() {
	this.$http.post('manterUser/inserir', {arg0: this.user}, {emulateJSON: true}).then(({ data }) => {
		//updateContent('content', data);
    });
};

this.methods.alterar = function() {
	this.$http.post('manterUser/alterar', {arg0: this.user}, {emulateJSON: true}).then(({ data }) => {
		//updateContent('content', data);
    });
};