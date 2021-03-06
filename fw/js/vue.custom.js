Vue.globalData = {};
Vue.pageCache = {};
Vue.contexts = {};

Vue.processApp = function(appName, template, dataComponent, dataRoot, methodsList, $appJs) {
	var el = null;
	if (template) {
		var component = {
			template : '<app>' + template + '</app>',
			data : dataComponent,
			methods : methodsList,
			dataRoot : dataRoot
		};

		if ($appJs) {
			$appJs.call(component, function(id, component) {
				el = id;
			});
		}

		component.target = el;		
		var data = component.data;
		component.data = function() {
			return data;
		};

		Vue.component(appName, component);
	} else {
		el = Vue.options.components[appName].options.target;
	}

	if (el) {
		if (el in Vue.contexts) {
			var context = Vue.contexts[el];
			context.currentView = appName;

			Vue.nextTick(function() {
				Vue.util.merge(context, dataRoot);
				Vue.util.merge(context.$refs[appName], dataComponent);
			});
		} else {
			dataRoot.currentView = appName;
			Vue.contexts[el] = new Vue({
				el : el,
				data : dataRoot,
				mixins : [ {
					data : Vue.globalData
				} ]
			});
		}
	}
};
/*
 * Vue.event = (function() { var eventsOnLoad = []; var load = function() {
 * for(i in eventsOnLoad) { eventsOnLoad[i](); } };
 * 
 * var registerOnLoad = function(f) { eventsOnLoad.push(f); };
 * 
 * return { load: load, registerOnLoad: registerOnLoad, } })();
 */