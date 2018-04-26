Vue.mixin({
	methods: {
		goback: function() {
			history.go(-1);
		},
		redirect: function(url, param) {
			var appName = url;
			if (param) {
				if (typeof param === 'object') {
					param = {
						args: JSON.stringify([param])
					};
				} else {
					url += '/' + param;
					param = {};
				}
			} else {
				param = {};
			}

			param.cached = appName in Vue.options.components;

			url = Vue.CONTEXT_PATH + (url === '/' ? '' : url);
			this.$http.post(url, param, {
				emulateJSON: true
			}).then(function(data) {
				eval(data.body);

				if (url) {
					history.pushState(null, url, url);
				}
			});
		},
		request: function(url) {
			var params = [];
			var callback;
			for (var i = 0; ++i < arguments.length;) {
				var arg = arguments[i];
				if (!arg || arg instanceof Function) {
					callback = arg;
					break;
				}
				params.push(arg);
			}

			this.$http.post(url, {
				args: JSON.stringify(params)
			}, {
				emulateJSON: true
			}).then(function(data) {
				data = data.body;
				
				if(data && typeof data === 'string') {
					if(Vue.modalError) {
						Vue.modalError.show(data);
					}
				} else {				
					if (data) {
						if (data.d) {
							Vue.util.objectAssignDeep(this, data.d);
						}
	
						if (data.rd) {
							Vue.util.objectAssignDeep(this.$root, data.rd);
						}
	
						if (data.m) {
							var f = '';
							for (var i in data.m) {
								f += 'this.' + i + '(';
								var args = data.m[i];
								var isFirst = true;
								for (a in args) {
									var arg = args[a];
									if (isFirst) {
										isFirst = false;
									} else {
										f += ',';
									}
									f += typeof arg === 'string' ? "'" + arg.replace(/'/g, "\\'") + "'" : arg;
								}
								f += ');';
							}
							if (f) {
								eval(f);
							}
						}
					}
	
					if (callback) {
						callback.call(this, data ? data.ds : null);
					}
				}
			});
		}
	}
});