define(["dojo/_base/declare", "dijit/form/FilteringSelect"], function(declare, FilteringSelect) {
	// module:
	// zwei/form/FilteringJsonSelect
	return declare("zwei.form.FilteringJsonSelect", [FilteringSelect], {
		url : null,
		items : null,
		unbounded : null,
		iteratedLevels : 0,
		maximum: null,
		constructor : function(args) {
			dojo.mixin(this, args);
			this.inherited(arguments);
			this.loadItems();
		},
		setUrl: function(url) {
			this.url = url;
			this.loadItems();
			this.set('value', '');
		},
		_finishedLoadItems : function()
		{
			console.log(this.data);	
		},
		_finishedLoadItemsByQuery : function()
		{
			console.log(this.data);	
		},
		loadItems : function() {
			var self = this;
			dojo.xhrGet({
				url : this.url,
				handleAs : 'json',
				load : function(items) {
					self.items = items;
					self.refreshValues(items);
					self._finishedLoadItems();
				},
				error : function(e) {
					utils.showMessage(e.message, 'error');
				}
			});	
		},
		loadItemsByQuery : function(pattern) {
//			this.loadItems(); 
			var self = this;
			var data = [];
			var pieces = pattern.split(".");
			var results = [];

			require(["dojo/store/Memory", "dojox/json/query", "dojo/keys"], function(Memory, query, keys) {
				try {
					results = query("$." + pattern, self.items);
				} catch (e) {
					console.error(e.message);
				}
				var length = pieces.length;

				self.items = {};
				
				if (length === 1) {
					self.items[pattern] = results;
				} else if (length === 2) {
					self.items[pieces[0]] = {};
					self.items[pieces[0]][pieces[1]] = results;
				} else if (length === 3) {
					self.items[pieces[0]] = {};
					self.items[pieces[0]][pieces[1]] = {};
					self.items[pieces[0]][pieces[1]][pieces[2]] = results;
				} else if (length === 4) {
					self.items[pieces[0]] = {};
					self.items[pieces[0]][pieces[1]] = {};
					self.items[pieces[0]][pieces[1]][pieces[2]] = {};
					self.items[pieces[0]][pieces[1]][pieces[2]][pieces[3]] = results;
				} else if (length === 5) {
					self.items[pieces[0]] = {};
					self.items[pieces[0]][pieces[1]] = {};
					self.items[pieces[0]][pieces[1]][pieces[2]] = {};
					self.items[pieces[0]][pieces[1]][pieces[2]][pieces[3]] = {};
					self.items[pieces[0]][pieces[1]][pieces[2]][pieces[3]][pieces[4]] = results;
				}




				for (var index in results) {
					data.push({
						id : pattern + '.' + index,
						name : pattern + '.' + index
					});
				}
				
				self.store = new Memory({
					data : data
				});
				self._finishedLoadItemsByQuery();
			});
		},
		postCreate : function() {
			this.inherited(arguments);
			this.refreshValues();
			if (this.maximum === null || this.maximum > 1) {//actualmente solo soporta restriccion en un nivel "this.maximum=1"
				dojo.connect(this.domNode, 'onkeyup', this, 'refreshValues');
			}
		},
		refreshValues : function(evt) {
			var text = this.textbox.value;
			this.iterateLevels(text, this.items, evt);
			if (this.metavalue && this.metavalue !== '') {
				this.set('value', this.metavalue);
			}
			//this.setStore(this.store);
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
					var backspacePressed = evt.keyCode === dojo.keys.BACKSPACE;
					
					dojo.forEach(levels, function(piece) {
						if (isNaN(piece)) {
							basePath += "." + piece;
						} else {
							basePath += "[" + piece + "]";
						}
					});
					var basePath = levels.join(".");

					if (self.unbounded && isDot && !backspacePressed && text.match("^(.*)\\[.*")) {
						var value = text.match("^(.*)\\[.*")[1];
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
