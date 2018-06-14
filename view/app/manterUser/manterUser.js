App("#app");

var msg = {
	'$alterar' : {
		success : 'Atualizado com sucesso.',
		error : 'Erro ao tentar atualizar.'
	},
	'$inserir' : {
		success : 'Cadastrado com sucesso.',
		error : 'Erro ao tentar cadastrar.'
	},
}

this.methods.manter = function() {
	if (!this.entity.name) {
		Modal.message('Campo nome é obrigatório.');
		return;
	}

	var $this = this;
	var action = $this.entity.id ? '$alterar' : '$inserir';
	this[action]($this.entity, function(data) {
		var onCloseModal = !data ? null : function() {
			if (data) {
				if ($this.entity.id) {
					$this.goback();
				} else {
					Vue.util.clear($this.entity);
				}
			}
		};

		Modal.message(msg[action][data ? 'success' : 'error'], onCloseModal);
	});
};