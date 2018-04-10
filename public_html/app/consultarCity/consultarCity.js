App("#app");

this.data.entitysCheckeds = [];

this.methods.selecionarTodos = function(e) {
	if(!e.target.checked) {
		this.entitysCheckeds = [];
	} else {
		for(var i in this.entitys) {
			this.entitysCheckeds.push(this.entitys[i]);	
		}	
	}
};

this.methods.remover = function(entity) {
	var $this = this;
	Modal.messageConfirm("Tem certeza que deseja remover a cidade '"+entity.name+"'?", function() {
		$this.$deletar(entity, function(deleted) {
			var msg;
			if(deleted) {
				msg = "Cidade '" + entity.name + "' removido com sucesso";
				this.entitys = this.entitys.filter(function (item) {
				    return entity.id !== item.id;
				});
			} else {
				msg = "Erro ao tentar remover a cidade '" + entity.name + "'.";
			}
			Modal.message(msg);
		});
	});
};

this.methods.deletarTodos = function() {
	if(this.entitysCheckeds.length === 0) {
		Modal.message('Selecione pelo menos uma cidade.');
		return;
	}
	
	var $this = this;
	Modal.messageConfirm("Tem certeza que deseja remover todos registros selecionados?", function() {
		$this.$deletarSelecionados($this.entitysCheckeds, function(deleted) {
			var msg;
			if(deleted) {
				msg = 'Todos registros selecionados foram removidas com sucesso.';				
				for(var i in this.entitysCheckeds) {
					Vue.delete(this.entitys, this.entitys.indexOf(this.entitysCheckeds[i]));
				}
				
				this.entitysCheckeds = [];
			} else {
				msg = 'Erro ao tentar remover todos os registros selecionados.';
			}
			Modal.message(msg);
		});
	});
};