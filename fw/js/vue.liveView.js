Vue.liveView = {
	checkModification : function(app) {
		Vue.http.get(Vue.CONTEXT_PATH + 'check?app=' + app, {
			before : function(request) {
				if (this.previousRequest) {
					this.previousRequest.abort();
				}

				this.previousRequest = request;
			}
		}).then(function(data) {
			if (data.body) {
				location.reload();
			}
		});
	}
};