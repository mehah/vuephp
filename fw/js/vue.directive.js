Vue.directive('goto', {
	bind: function(el, binding, vnode) {
		var url = Vue.CONTEXT_PATH + binding.value;

		el.setAttribute('href', url);

		el.handler = function(e) {
			if (e) e.preventDefault();
			vnode.context.redirect(binding.value);
		};
		el.addEventListener('click', el.handler);
	},
	unbind: function(el, binding, vnode) {
		el.removeEventListener('click', el.handler)
	}
});