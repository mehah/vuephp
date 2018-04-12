Vue.liveView = {
	id: 0,
	checkModification: function(app) {
		Vue.http.get(Vue.CONTEXT_PATH + 'check?app=' + app).then(function(data) {
			if(data.body) {
				location.reload();  
			} else {
				Vue.liveView.id = setTimeout(function() {
					Vue.liveView.checkModification(app);
				}, 1000);
			}
		});
	}
};