define(["dojo/_base/declare", "dijit/form/FilteringSelect"], function(declare, FilteringSelect) {
	// module:
	// zwei/form/FilteringJsonSelect
	return declare("zwei.form.FilteringJsonSelect", [FilteringSelect], {
		url : null,
		items : null,
		unbounded : null,
		constructor : function(args) {
			dojo.mixin(this, args);
			this.inherited(arguments);
			var self = this;
			dojo.xhrGet({
				url : args.url,
				handleAs : 'json',
				load : function(items) {
					self.items = items;
					self.refreshValues(items);
				},
				error : function(e) {
					utils.showMessage(e.message, 'error');
				}
			});

		},
		postCreate : function() {
			this.inherited(arguments);
			this.refreshValues();
			dojo.connect(this.domNode, 'onkeyup', this, 'refreshValues');
		},
		refreshValues : function(evt) {
			var url = this.url;
			var text = this.textbox.value;
			this.iterateLevels(text, this.items, evt);
			//self.set('store', self.store);
		},
		iterateLevels : function(text, items, evt) {
			var self = this;
			var levels = text.split(".");
			var isDot = text[text.length - 1] === ".";
			var isLeftSquareBracket = text[text.length - 1] === "[";
			var data = [];
			var isIndex = false;
			var basePath = '';
			
			require(["dojo/store/Memory", "dojox/json/query", "dojo/keys"], function(Memory, query, keys) {
				if (self.unbounded) {
					isIndex = text.match("\\[([0-9]+)$");
					if (isIndex) {
						data.push({
							id : text + "]",
							name : text + "]"
						});
					}
				}

				if (isDot || isLeftSquareBracket || (typeof evt !== 'undefined' && evt.keyCode == dojo.keys.BACKSPACE)) {
					var basePath = '';
					var value = '';
					dojo.forEach(levels, function(piece) {
						if (isNaN(piece)) {
							basePath += "." + piece;
						} else {
							basePath += "[" + piece + "]";
						}
					});
					var basePath = levels.join(".");

					if (self.unbounded && isDot && text.match("^(.*)\\[.*")) {
						var value = text.match("^(.*)\\[.*")[1];
						
						if (evt.keyCode == dojo.keys.BACKSPACE) {
							console.log('que no haga boing');
						}


						var results = query("$." + value + '[0]', items);
					} else {
						var results = query("$." + basePath.slice(0, -1), items);
					}

					var first = true;
					var firstIndex = null;
					for (var index in results) {
						if (typeof results === 'object') {
							if (results instanceof Array) {
								value = basePath.substring(0, basePath.length - 1) + "[" + index + "]";
								data.push({
									id : value,
									name : value
								});

								if (first) {
									firstIndex = index;
									first = false;
								}
							} else if (isDot) {
								value = basePath + index;
								data.push({
									id : value,
									name : value
								});
							}
						}

					}
					if (firstIndex) {
						self.set('value', basePath.substring(0, basePath.length - 1) + "[" + firstIndex + "]");
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
