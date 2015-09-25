dojo.declare("zwei.ExtraData", null, {
	container : null,
	idName : null,
	idValue : null,
	dijitFormSearch : null,
	dijitDialog : null,
	tabId : null,
	openedTabs : [],
	resizedLayouts : [],
	constructor : function(args) {
		dojo.mixin(this, args);
	},
	reset : function(tabId) {
		if ( typeof tabId === 'undefined') {
			this.openedTabs = [];
		} else {
			var index = this.openedTabs.indexOf(tabId);
			if (index > -1) {
				this.openedTabs = this.openedTabs.splice(index, 1);
			}
		}
		this.resizedLayouts = [];
	},
	initTab : function(tab, idName, component) {
		var domOpc = globalOpc === 'clone' ? 'add' : globalOpc;
		var domPrefixAndId = tab.id.split('tab_' + domOpc + '_ctrl');
		this.tabId = tab.id;

		this.form = dijit.byId(tab.parentNode.id);

		this.container = dijit.byId(domPrefixAndId[0] + 'tab_' + domOpc + domPrefixAndId[1]);
		this.idName = idName;
		this.idValue = this.form.getChildrenByName('data[id]').length ? 
			this.form.getChildrenByName('data[id]')[0].get('value') : 
			null;
		this.component = component;
	},
	resizeContent : function(tab) {
		var domOpc = globalOpc === 'clone' ? 'add' : globalOpc;
		var domPrefixAndId = tab.id.split('tab_' + domOpc + '_ctrl');
		this.tabId = tab.id;

		this.form = dijit.byId(tab.parentNode.id);

		this.container = dijit.byId(domPrefixAndId[0] + 'tab_' + domOpc + domPrefixAndId[1]);
		if (this.resizedLayouts.indexOf(this.container.get('id'))) {
			this.container.resize();
			this.resizedLayouts.push(this.container.get('id'));
		}
	},
	isOpenedList : false,
	list : function(tabId, idName) {
		dojo.require('zwei.utils.String');
		var domPrefix = this.component.toVarWord();
		
		if (dijit.byId(domPrefix + 'dataGrid')) {
			dijit.byId(domPrefix + 'dataGrid').destroyRecursive();
		}

		if (typeof tabId === 'undefined')
			var tabId = false;

		if (typeof idName != 'undefined') {
			this.idValue = this.form.getChildrenByName('data[' + idName + ']').length ? 
				this.form.getChildrenByName('data[' + idName + ']')[0].get('value') : 
				null;
		}

		if (!tabId || this.openedTabs.indexOf(tabId) < 0) {
			this.container.set('href', base_url
					+ 'components/extra-data?search[' + this.idName
					+ '][value]=' + this.idValue + '&search['
					+ this.idName + '][operator]=%3D&p='
					+ this.component + '&' + this.idName + '=' + this.value);
			this.openedTabs.push(this.tabId);
		}
	},
	listAttributes : function(tabId) {
		if ( typeof tabId === 'undefined')
			var tabId = false;

		if (!tabId || this.openedTabs.indexOf(tabId) < 0) {
			this.container.set('href', base_url + 'components/extra-data/list-attributes?search[' + this.idName + '][value]=' + this.idValue + '&search[' + this.idName + '][operator]=%3D&p=' + this.component);

			this.openedTabs.push(this.tabId);
		}
	},
	editAttributes : function(tabId, idName, idParentEditorName, hashName) {
		dojo.require('dojo.aspect');

		if ( typeof tabId === 'undefined')
			var tabId = false;
		if ( typeof idParentEditorName === 'undefined')
			var idParentEditorName = null;

		if ( typeof idName != 'undefined') {
			this.idValue = this.form.getChildrenByName('data[' + idName + ']').length ? this.form
			.getChildrenByName('data[' + idName + ']')[0].get('value') : null;
		}

		if (!tabId || this.openedTabs.indexOf(tabId) < 0) {
			admportal.lockScreen();
			this.container.set('href', base_url + 'components/extra-data/edit-attributes?search[' + this.idName + '][value]=' + this.idValue + '&search[' + this.idName + '][operator]=%3D&hashName=' + hashName + '&p=' + this.component + ( idParentEditorName ? '&idParentEditorName=' + idParentEditorName : ''));
			dojo.aspect.after(this.container, 'onLoad', function() {
				setTimeout(function() {
					admportal.lockScreen(false);
				}, 600);
			});
			this.openedTabs.push(this.tabId);
		}
	},
	edit : function(e, dataGrid) {
		// dojox.grid.cells.Editor
		dataGrid.edit.setEditCell(e.cell, e.rowIndex);
	},
	applyEdit : function(model, inValue, dataGrid, idParentEditorName) {
		var rowSelected = dataGrid.selection.getSelected()[0];
		var name = rowSelected.name[0];
		if (!model) {
			//utils.showMessage('Se ha actualizado el valor de <strong>' + name + '</strong> a &quot;' + inValue + '&quot;', 'warning');
		} else {
			var myId = rowSelected.id[0];
			var xhrContent = {
				model : model,
				action : 'edit',
				format : 'json',
				'data[value]' : inValue,
				'primary[id]' : myId,
				p : this.component
			};

			if (idParentEditorName) {
				xhrContent['data[' + idParentEditorName + ']'] = this.form
				.getChildrenByName('data[id]')[0].get('value');
			}

			dojo.xhrPost({
				url : base_url + 'crud-request',
				content : xhrContent,
				sync : true,
				preventCache : true,
				handleAs : 'json',
				timeout : 5000,
				load : function(response) {
					console.debug(response);
					if (response.state == 'UPDATE_OK') {
						utils.showMessage('Actualizado valor de <strong>' + name + '</strong> a &quot;' + inValue + '&quot;', 'warning');
					}
				},
				error : function(message) {
					utils.showMessage('Error en comunicacion de datos. error: ' + message, 'error');
					return false;
				}
			});
		}

	},
	form : {
		form : null,
		init : function(form) {
			this.form = form;
			this.setRegexp();
			this.setList();
		},
		setRegexp : function() {
			this.form.getChildrenByName('data[regExp]')[0].set('disabled', this.form.getChildrenByName('data[type]')[0].get('value') !== 'dijit.form.ValidationTextBox');
		},
		setList : function() {
			this.form.getChildrenByName('data[list]')[0].set('disabled', (this.form.getChildrenByName('data[type]')[0].get('value') !== 'dijit.form.FilteringSelect' && this.form
			.getChildrenByName('data[type]')[0].get('value') !== 'dijit.form.CheckBox'));
		}
	},
	format : function(data, iRowIndex, cell) {
		dojo.require('dijit.form.DateTextBox');
		dojo.require('dijit.form.TimeTextBox');

		var grid = dijit.byId(this.id.substring(0, this.id.length - 4));
		var hashName = dojo.byId(this.id.substring(0, this.id.length - 4) + '_hash').getAttribute('name');

		var type = grid._by_idx[iRowIndex].item.type.toString();
		var id = grid._by_idx[iRowIndex].item.id.toString();

		var idInput = grid.get('id') + iRowIndex;
		if (dijit.byId(idInput))
			dijit.byId(idInput).destroyRecursive();

		var widget;

		if (type === 'dijit.form.CheckBox') {
			var value = '1';
			var uncheckedvalue = '0';
			var list = grid._by_idx[iRowIndex].item.list.toString();
			var items = list.split(",");
			if (items.length === 2) {
				value = items[0];
				uncheckedvalue = items[1];
			}

			widget = new dijit.form.CheckBox({
				id : idInput,
				value : value,
				checked : data == value
			});
			widget.set('uncheckedvalue', uncheckedvalue);
		} else if (type === 'dijit.form.DateTextBox') {
			widget = new dijit.form.DateTextBox({
				id : idInput,
				value : data != '' ? data : null
			});
		} else if (type === 'dijit.form.TimeTextBox') {
			widget = new dijit.form.TimeTextBox({
				id : idInput,
				constraints : {
					timePattern : 'HH:mm:ss',
					clickableIncrement : 'T00:15:00',
					visibleIncrement : 'T00:15:00',
					visibleRange : 'T01:00:00'
				},
				value : data != '' ? data : null
			});
		} else if (type === 'dijit.form.FilteringSelect') {
			var store = new dojo.store.Memory();
			var list = grid._by_idx[iRowIndex].item.list.toString();
			var items = list.split(",");

			for (var i = 0; i < items.length; i++) {
				store.add({
					id : items[i] != '' ? items[i] : ' ',
					name : items[i]
				});
			}
			if (items[0] == '')
				items[0] = ' ';
			widget = new dijit.form.FilteringSelect({
				id : idInput,
				value : data ? data : items[0],
				store : store,
				required : false
			});
		} else {
			var regExp = grid._by_idx[iRowIndex].item.regExp.toString();
			widget = new dijit.form.ValidationTextBox({
				id : idInput,
				value : data,
				onChange : function(e) {
					console.debug(e);
				}
			});
			if (regExp !== '') {
				widget.set('regExp', regExp);
			}
		}

		widget.set('name', 'data[' + hashName + '][' + id + ']');
		//widget.set('required', true);
		//console.debug(widget);

		widget._destroyOnRemove = true;
		return widget;
	},
	applyEditAttribute : function(model, inValue, dataGrid) {
		var rowSelected = dataGrid.selection.getSelected()[0];
		var myId = rowSelected.id[0];
		var name = rowSelected.name[0];

		var xhrContent = {
			model : model,
			action : 'edit',
			format : 'json',
			'data[name]' : name,
			p : this.component,
			'primary[id]' : myId
		};

		dojo.xhrPost({
			url : base_url + 'crud-request',
			content : xhrContent,
			sync : true,
			preventCache : true,
			handleAs : 'json',
			timeout : 5000,
			load : function(response) {
				console.debug(response);
				if (response.state == 'UPDATE_OK') {
					utils.showMessage('Se ha actualizado el valor de <strong>' + name + '</strong>');
				}
			},
			error : function(message) {
				utils.showMessage('Error en comunicacion de datos. error: ' + message, 'error');
				return false;
			}
		});
	},
	formatValidationTextBox: function(value){
		var widget = new dijit.form.ValidationTextBox({
			value : value,
			onChange : function(e) {
				console.debug(e);
			}
		});
		widget.set('name', 'data[extradata][]');
		widget.set('required', false);
		console.debug(widget);

		widget._destroyOnRemove = true;
		return widget;
	},
	addTupleToGrid : function(extraDataGrid, values) {
		var added = false;
		var item = {
			name: values['data[name]'], 
			value: values['data[value]']
		};
		var data = {identifier: 'name', label: 'value'};
		
		data.items = [];
		
		if (extraDataGrid.store) {
			extraDataGrid.store.fetch({
				onComplete: getAndAddItem
			});
		} else {
			store = new dojo.data.ItemFileWriteStore({data: data});
			addItem(item);
		}
		
		function getAndAddItem(items){
			data.items = items;
			store = new dojo.data.ItemFileWriteStore({data: data}); 
			addItem(item);
		}
		
		function addItem(item) {
			added = store.newItem(item);
		} 
		
		extraDataGrid.setStore(store);
		return added;
	},
	removeVars : function(model, dataGrid) {
		var self = this;
		var items = dataGrid.selection.getSelected();

		if (items.length) {
			var message = items.length > 1 ? '¿Borrar ' + items.length + ' elementos seleccionados?' : '¿Borrar un elemento seleccionado';

			if (confirm(message)) {
				if (!model) {
					for (var j = 0; j < items.length; j++) {
						dataGrid.store.deleteItem(items[j]);
					}
				} else {
					for (var j = 0; j < items.length; j++) {
						if (items[j].i != undefined && items[j].r._items != undefined) {
							items[j] = items[j].i;
						}

						var xhrContent = {
							model : model,
							action : 'delete',
							format : 'json'
						};

						xhrContent['primary[id]'] = items[j].id;

						dojo.xhrPost({
							url : base_url + 'crud-request',
							content : xhrContent,
							handleAs : 'json',
							sync : true,
							preventCache : true,
							timeout : 5000,
							load : function(response) {
								if (response.state == 'DELETE_OK') {
									utils.showMessage('Se ha borrado correctamente.');
									var newStore = new dojo.data.ItemFileWriteStore({
										url : dataGrid.store.url
									});
									dataGrid.setStore(newStore);

								} else if (response.state == 'DELETE_FAIL') {
									utils.showMessage('Ha ocurrido un error, verifique datos o intente más tarde', 'error');
								}
							},
							error : function(message) {
								utils.showMessage('Error en comunicacion de datos. error: ' + message, 'error');
								return false;
							}
						});
					}
				}
			}
		} else {
			utils.showMessage('Ningún elemento seleccionado', 'error');
		}
	},
	validate : function(dijitForm) {
		var validated = true;

		dojo.query('#' + dijitForm.id + ' [data-dojo-type$="ContentPane"] input').forEach(function(input) {
			if (dijit.byId(input.id))
				console.debug(dijit.byId(input.id));
			if (dijit.byId(input.id) && typeof dijit.byId(input.id).isValid == "function" && !dijit.byId(input.id).isValid()) {

				var tabContainerId = dijit.byId(input.id).getParent().getParent().getParent().get('id');
				var tabCtrl = dojo.byId(tabContainerId.replace(globalOpc, globalOpc + "_ctrl"));
				tabCtrl.click();
				validated = false;
				return false;
			}
		});
		return validated;
	}

});