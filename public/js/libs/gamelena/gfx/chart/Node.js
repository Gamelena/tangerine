dojo.declare("gamelena/gfx/chart/Node", null, {
	component: null,
	connectorComponent: null,
	domPrefix: null,
	nodeId: null,
	nodesModel: 'TreeModel',
	menu: null,
	labelName: 'name',
	primary: [],
	message: {
		'edit' : 'Editar Nodo',
		'delete' : 	'Eliminar Nodo'
	},
	fill: 
	{
		type : "radial",
		cx : 0,
		cy : 0,
		colors : [ {
			offset : 0,
			color : "rgb(162, 253, 0)"
		}, {
			offset : 1,
			color : "#096301"
		} ]
	},
	/**
	 * Inicializador de objeto
	 */
	constructor: function(args) {
		dojo.declare.safeMixin(this, args);
	},
	menu : function() {
		var primaryKey;
		var primary;
		for (var index in this.primary) {
			primaryKey = index;
			primary = this.primary[index];
		}
		var self = this;
		require([ 'dijit/Menu', 'dijit/MenuItem', 'dijit/MenuSeparator', 'gamelena/Form'], function(Menu, MenuItem, MenuSeparator, Form) {
			self.menu = new dijit.Menu({
				targetNodeIds : [ self.nodeId ]
			});
			self.menu.addChild(new MenuItem({
				label : self.message['edit'],
				iconClass : "dijitIconEdit",
				onClick : function() {
					self.groups[self.nodeId].moveToFront();
					var form = new Form({
						component : self.component,
						ajax : true,
						action : 'edit',
						primary : {
							'id' : self.nodeId
						},
						queryParams: 'p=' + self.component,
						dijitDialog : dijit.byId(self.domPrefix + 'dialog_edit'),
						dijitForm : dijit.byId(self.domPrefix + 'form_edit')
					});
					globalOpc = 'edit';//<- @fixme, globalOpc debiera estar modificada en es esto es un workaround
					form.showDialog();
				}
			}));
			self.menu.addChild(new MenuItem({
				label : self.message['delete'],
				iconClass : "dijitIconDelete",
				onClick : function() {
					if (confirm(self.message['delete'] + nodeId)) {
						self['delete'](nodeId, self.nodesModel, 'id');
					}
				}
			}));
			
			self.menu.addChild(new MenuSeparator());
			
			self.menu.addChild(new MenuItem({
				label : "Nuevo Enlace",
				iconClass : "dijitIconConnector",
				onClick : function() {
					self.groups[nodeId].moveToFront();
					var form = new Form({
						component : 'enlaces.xml',
						ajax : true,
						action : 'add',
						queryParams : 'servicios_id=' + self.id + '&nodes_id_from='
								+ nodeId,
						primary : {
							'id' : nodeId
						},
						dijitDialog : dijit.byId('enlaces_xmldialog_add')
					});
					form.showDialog();
				}
			}));

			self.menu.startup();
		})
	},
	add: function(data, pathsId, id, x1, y1) {
		var self = this;
		dojo.require(['gamelena/gfx/ZoomMover', 'dojox/gfx/Moveable'], function(ZoomMover, Moveable){
			var label = data[self.labelName];
			var self = this;

			if (globalOpc == 'add' || rebuild) {
				this.x1 = (typeof (x1) == 'undefined') ? 100 : x1;
				this.y1 = (typeof (y1) == 'undefined') ? 100 : y1;
				if (typeof (globalNodeType) == 'undefined') {
					var globalNodeType = 1;
				}

				var nodeId = typeof (id) == 'undefined' || id == null ? data.lastInsertedId : id;

				this.groups[nodeId] = this.surface.createGroup();
				this.group.add(this.groups[nodeId]);
				console.debug(nodeId);

				// our geometry
				this.r = 50;

				if (label != null) {
					console.debug(label);
					this.circles[nodeId] = this.surface.createCircle({
						cx : 0,
						cy : 0,
						r : this.r
					}).setFill(self.fill);

					var textPt = '33pt', textX = 60, textY = 16;

					this.circlesLabels[nodeId] = this.surface.createText({
						x : textX,
						y : textY,
						text : utils.htmlEntityDecode(label),
						align : "center"
					}).setFont({
						family : "Arial, Courier",
						size : textPt,
						weight : "bold"
					}).setFill("#243C5F");
					this.circlesLabels[nodeId].rawNode.id = 'label' + nodeId;
					this.circlesLabels[nodeId].rawNode.style.cursor = 'pointer';
					this.circlesLabels[nodeId].connect("ondblclick", function(e) {
						var form = new gamelena.Form({
							component : 'mensajes.xml',
							ajax : true,
							action : 'edit',
							primary : {
								'id' : nodeId
							},
							dijitDialog : dijit.byId('mensajes_xmldialog_edit'),
							dijitForm : dijit.byId('mensajes_xmlform_edit'),
							queryParams : 'servicios_id=' + self.id
						});
						globalOpc = 'edit';//<- @fixme, globalOpc debiera estar modificada en este punto, esto es un workaround
						form.showDialog();
						self.groups[nodeId].moveToFront();
					});

					this.groups[nodeId].add(this.circles[nodeId]);
					this.groups[nodeId].add(this.circlesLabels[nodeId]);
					this.groups[nodeId].setTransform(dojox.gfx.matrix.translate(this.x1,
							this.y1));
					this.moves[nodeId] = new dojox.gfx.Moveable(this.groups[nodeId], {
						mover : gamelena.gfx.ZoomMover
					});

					dojo.connect(this.moves[nodeId], "onMoveStart", function(mover, shift) {
						self.groups[nodeId].moveToFront();
						$globalNodeId = nodeId;
					});

					dojo.connect(this.moves[nodeId], "onMoveStop", function(mover, shift) {
						if (self.groups[nodeId].matrix != null) {
							self.saveNode(nodeId, response);
						}
					});

					this.circles[nodeId].connect("ondblclick", function(e) {
						var form = new gamelena.Form({
							component : 'mensajes.xml',
							ajax : true,
							action : 'edit',
							primary : {
								'id' : nodeId
							},
							dijitDialog : dijit.byId('mensajes_xmldialog_edit'),
							queryParams : 'servicios_id=' + self.id
						});
						globalOpc = 'edit';//<- @fixme, globalOpc debiera estar modificada en este punto, esto es un workaround
						form.showDialog();
						self.groups[nodeId].moveToFront();
					});

					this.circles[nodeId].rawNode.id = 'circle' + nodeId;
					this.circles[nodeId].rawNode.style.cursor = 'pointer';
					this.circles[nodeId].moveToFront();
					this.circlesLabels[nodeId].moveToFront();
					this.nodeMenu('circle' + nodeId);
					this.nodeMenu('label' + nodeId);
				}
			} else {
				console.debug('else');
				id = parseInt(response.more.where.id);
				console.debug(rebuild);
				this.updateNode(id, label);
			}
		});
	},
	save: function() {
		var myX = this.group.matrix.dx;
		var myY = this.group.matrix.dy;
		dojo.xhrPost({
			url : base_url + 'crud-request',
			content : {
				'data[x]' : myX,
				'data[y]' : myY,
				'primary[id]' : nodeId,
				'action' : 'edit',
				'model' : this.model,
				'format' : 'json'
			},
			handleAs : 'json',
			sync : true,
			preventCache : true,
			timeout : 5000,
			load : function(response) {
				if (response.state == 'UPDATE_FAIL') {
					console.error(response);
					// dijit.byId('firstToaster').setContent('¡Ups! a ocurrido un
					// error al actualizar los datos.', 'fatal');
				} else {
					utils.showMessage('<b>Posición Actualizada</b><br/> Nodo id '
							+ nodeId + " (" + myX + "," + myY + ") ", 'message');
				}
			},
			error : function(err) {
				console.debug(err);
				utils.showMessage(err.message, 'error');
			}
		});
	},
	'delete': function(nodeId, model, id, alias) {
		var self = this;
		if (typeof (alias) == 'undefined')
			var alias = 'Elemento';

		var myContent = {};
		myContent['primary[' + id + ']'] = nodeId;
		myContent['action'] = 'delete';
		myContent['model'] = model;
		myContent['format'] = 'json';

		dojo
		.xhrPost({
			url : base_url + 'crud-request',
			content : myContent,
			handleAs : 'json',
			sync : true,
			preventCache : true,
			timeout : 5000,
			load : function(respuesta) {
				utils.showMessage(
						'<b/>' + alias + ' ' + nodeId + ' Eliminado.');
				if (model == 'TreeNodesModel') {
					self.circlesLabels[nodeId].removeShape();
					self.circles[nodeId].removeShape();
					self.groups[nodeId].destroy();
					self.moves[nodeId].destroy();
					console.debug(dojo.query('path[nodesidfrom="' + nodeId
							+ '"]'));
					dojo.query(
							'path[nodesidfrom="' + nodeId
									+ '"], path[nodesidto="' + nodeId
									+ '"]').forEach(
							function(myPath, i) {
								self.deleteNode(myPath.id.replace(/^\D+/g, ''),
										'LinksModel', 'id', 'enlace');
							});

				} else if (model == 'LinksModel') {
					self.linesGroups[nodeId].removeShape();
					delete self.lines[nodeId];
				}
				if (self.supportsAudio()) {
					var a = document.createElement('audio');
					return !!(a.canPlayType && a.canPlayType(
							'audio/wav; codecs="1"').replace(/no/, ''));
				}
			},
			error : function(e) {
				console.error(e);
				utils.showMessage(e.message, 'error');
			}
		});
	},
	updateNode: function(nodeId, nombre) {
		var self = this;
		this.x = this.circlesLabels[nodeId].shape.x;
		this.y = this.circlesLabels[nodeId].shape.y;
		this.circlesLabels[nodeId].removeShape();
		var textPt = '33pt', textX = 60, textY = 16;

		this.circlesLabels[nodeId] = this.surface.createText({
			x : textX,
			y : textY,
			text : utils.htmlEntityDecode(nombre),
			align : "center"
		}).setFont({
			family : "Arial, Courier",
			size : textPt,
			weight : "bold"
		}).setFill("#ffffff");
		this.circlesLabels[nodeId].rawNode.id = 'label' + nodeId;
		this.circlesLabels[nodeId].rawNode.style.cursor = 'pointer';
		this.circlesLabels[nodeId].connect("ondblclick", function(e) {
			var form = new gamelena.Form({
				component : 'mensajes.xml',
				ajax : true,
				action : 'edit',
				primary : {
					'id' : nodeId
				},
				dijitDialog : dijit.byId('mensajes_xmldialog_edit'),
				dijitForm : dijit.byId('mensajes_xmlform_edit')
			});
			globalOpc = 'edit';//<- @fixme, globalOpc debiera estar modificada en este punto, esto es un workaround
			form.showDialog();
			self.groups[nodeId].moveToFront();
		});

		this.groups[nodeId].add(this.circlesLabels[nodeId]);
		this.groups[nodeId].moveToFront();
	}
});