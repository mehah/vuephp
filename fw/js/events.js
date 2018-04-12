window.onpopstate = function(event) {
	Vue.http.post(event.target.location.pathname, {
		cached: true
	}, {
		emulateJSON: true
	}).then(function(data) {
		eval(data.body);
		if(Vue.liveView) {
			Vue.liveView.checkModification(location.pathname.substring(Vue.CONTEXT_PATH.length));
		}
	});
};