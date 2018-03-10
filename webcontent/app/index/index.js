this.methods.cadastrar = function() {
	this.$http.get('manterUser').then(({ data }) => {
		updateContent('content', data);
    });
};

this.methods.consultar = function() {
	this.$http.get('consultarUser').then(({ data }) => {
		updateContent('content', data);
    });
};