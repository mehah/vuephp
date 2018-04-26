Vue.directive('goto', {
	bind : function(el, binding, vnode) {
		var url = Vue.CONTEXT_PATH + binding.value;

		var hasHref = el.hasAttribute('href');

		var hash = '';
		if (hasHref) {
			if(el.getAttribute('href').indexOf('#') == 0) {
				if((hash = el.getAttribute('href')) === '#') {
					hash = '';
				}
				hasHref = false;
			}
		} else {
			el.setAttribute('href', url);
		}

		el.handler = function(e) {
			if (!hasHref && e) {
				e.preventDefault();
			}
			
			if (url.indexOf(location.href.pathname) === -1) {
				vnode.context.redirect(binding.value+hash);
			}
		};
		el.addEventListener('click', el.handler);
	},
	unbind : function(el, binding, vnode) {
		el.removeEventListener('click', el.handler)
	}
});