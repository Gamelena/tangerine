require(['dojo/_base/declare', "dijit/form/FilteringSelect"], function(declare, FilteringSelect) {
	declare("zwei/form/FilteringJsonSelect", [FilteringSelect], {
		url : null,
		constructor : function(args) {
			dojo.mixin(this, args);
			this.inherited(arguments);
		},
		postCreate : function() {
			this.inherited(arguments);
			this.refreshValues();
			dojo.connect(this.domNode, 'onkeypress', this, 'refreshValues');
		},
		refreshValues : function() {
			var url = this.url;
			var self = this;
			dojo.xhrGet({
				url : url,
				handleAs : 'json',
				load : function(items) {
					self.items = items;
					var text = self.textbox.value;
					self.iterateLevels(text, items);
					self.set('store', self.store);
				},
				error : function(e) {
					utils.showMessage(e.message, 'error');
				}
			});
		},
		iterateLevels : function(text, items) {
			var self = this;
			var levels = text.split(".");
			var isDot = text[text.length - 1] === ".";
			var data = [];
			var basePath = '';
			require(["dojo/store/Memory", "dojox/json/query"], function(Memory, query) {
				if (isDot) {
					var basePath = levels.join(".");
					results = query("$." + basePath.slice(0, -1), items);
					for (var index in results) {
						if ( typeof results === 'object') {
							data.push({
								id : basePath + index,
								name : basePath + index
							});
						}
					}
				} else {
					for (var index in items) {
						if (index.match("^" + text.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&") + ".*")) {
							data.push({
								id : index,
								name : index
							});
						}
					}
				}

				if (data.length > 0) {
					self.store = new Memory({
						data : data
					});
				}
			});
		}
	});
}); 