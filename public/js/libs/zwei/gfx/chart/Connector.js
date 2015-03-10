dojo.declare("zwei/gfx/chart/Connector", null, {
	/**
	 * Inicializador de objeto, asocia los formularios pertinentes a éste.
	 */
	constructor: function(args) {
		dojo.declare.safeMixin(this, args);
	},
	linkMenu : function(id) {
		var self = this;
		require([ 'dijit/Menu', 'dijit/MenuItem', 'dijit/MenuSeparator',
				'zwei/Form' ], function(Menu, MenuItem, MenuSeparator, Form) {
			var pMenu;

			var nodeId = id.replace(/^\D+/g, '');
			pMenu = new Menu({
				targetNodeIds : [ id ]
			});
			pMenu.addChild(new MenuItem({
				label : "Editar Enlace",
				iconClass : "dijitIconEdit",
				onClick : function() {
					var form = new Form({
						component : 'enlaces.xml',
						ajax : true,
						action : 'edit',
						queryParams : 'servicios_id=' + self.id,
						primary : {
							'id' : nodeId
						},
						dijitDialog : dijit.byId('enlaces_xmldialog_edit')
					});
					form.showDialog();
				}
			}));
			pMenu.addChild(new MenuItem({
				label : "Eliminar Enlace",
				iconClass : "dijitIconDelete",
				onClick : function() {
					if (confirm('Borrar enlace ' + nodeId)) {
						self.deleteNode(nodeId, 'LinksModel', 'id', 'Enlace');
					}
				}
			}));
			pMenu.startup();
		});
	},
	addLink: function(response, pathsId) {
		console.debug(response);
		var id = typeof (response.more.lastInsertedId) != 'undefined' ? response.more.lastInsertedId
				: response.more.where.id;
		
		this.addConnector(id, pathsId, response.more.data.nodes_id_from,
				response.more.data.nodes_id_to, response.more.data.cost);
	},
	getConnectorPath: function(x1, y1, x2, y2, dx, dy, curveType) {
		var alpha = (Math.atan(Math.abs(y1 - y2) / Math.abs(x1 - x2)));
		var dx2 = this.radio * Math.cos(alpha);
		var dy2 = this.radio * Math.sin(alpha);

		var beta = 80.1 - alpha;//@fixme, debiera ser "var beta = 90 - alfa" pero se cambia el angulo para parchar diferencia en calculo x1, y1 
		var dx1 = this.radio * Math.sin(beta);
		var dy1 = this.radio * Math.cos(beta);

		if (x1 < x2) {
			x1 = x1 + dx1;
			x2 = x2 - dx2;
		} else {
			x1 = x1 - dx1;
			x2 = x2 + dx2;
		}
		
		if (y1 < y2) {
			y1 = y1 + dy1;
			y2 = y2 - dy2;
		} else {
			y1 = y1 - dy1;
			y2 = y2 + dy2;
		}
		
		var midX1 = (x1 + x2)*0.5;
		var midX2 = (x1 + x2)*0.5;
		var path =  "M" + x1 + "," + y1 + " C" + midX1 +"," + y1 + " " + midX2 + "," + y2 + " "+ x2 + "," + y2;
		return path;
	},
	moveArrowHelpers: function(x1, y1, x2, y2, id, group) {
		dojo.require('dojox.gfx.matrix');
		var _arrowHeight = 15;
		var _arrowWidth = 9;

		var alpha = (Math.atan(Math.abs(y1 - y2) / Math.abs(x1 - x2)));
		var dx2 = this.radio * Math.cos(alpha);
		var dy2 = this.radio * Math.sin(alpha);

		var beta = 80.1 - alpha;//@fixme, debiera ser "var beta = 90 - alfa" pero se cambia el angulo para parchar diferencia en calculo x1, y1 
		var dx1 = this.radio * Math.sin(beta);
		var dy1 = this.radio * Math.cos(beta);
		
		if (x1 < x2) {
			x1 = x1 + dx1;
			x2 = x2 - dx2;
		} else {
			x1 = x1 - dx1;
			x2 = x2 + dx2;
		}
		if (y1 < y2) {
			y1 = y1 + dy1;
			y2 = y2 - dy2;
		} else {
			y1 = y1 - dy1;
			y2 = y2 + dy2;
		}

		var len = Math.sqrt(Math.pow(x2 - x1, 2) + Math.pow(y2 - y1, 2));

		this.linesPointers[id].setShape({
			path : "M" + (len - _arrowHeight) + " 0 " + " L" + (len - _arrowHeight)
					+ " " + (-_arrowWidth) + " L" + len + " 0 " + " L"
					+ (len - _arrowHeight) + " " + _arrowWidth + " L"
					+ (len - _arrowHeight) + " 0"
		});
		this.linesLabels[id].setShape({
			x : (len / 1.3),
			y : -10
		});

		var _rot = Math.asin((y2 - y1) / len) * 180 / Math.PI;
		if (x2 <= x1) {
			_rot = 180 - _rot;
		}

		group.setTransform([ dojox.gfx.matrix.translate(x1, y1),
				dojox.gfx.matrix.rotategAt(_rot, 0, 0) ]);
	},
	drawPointer: function(x1, y1, x2, y2, id, cost) {
		var group = this.surface.createGroup();
		var _defaultStroke = {
			style : "Solid",
			width : 9,
			color : "#507869"
		};
		
		this.linesPointers[id] = group.createPath().setStroke(_defaultStroke).setFill(
				"#507869");
		this.linesPointers[id].moveToBack();
		
		this.linesLabels[id] = group.createText({
			text : cost,
			align : "center"
		}).setFont({
			family : "Arial, Verdana",
			size : "32pt",
			weight : "bold"
		}).setFill("#009900");
		
		this.linesLabels[id].moveToFront();
		
		this.moveArrowHelpers(x1, y1, x2, y2, id, group);
		
		group.connect("ondblclick", function(e) {
			var form = new zwei.Form({
				component : 'enlaces.xml',
				ajax : true,
				action : 'edit',
				queryParams : 'servicios_id=' + self.id,
				primary : {
					'id' : id
				},
				dijitDialog : dijit.byId('enlaces_xmldialog_edit')
			});
			form.showDialog();
		});
		
		group.rawNode.style.cursor = 'pointer';
		group.rawNode.id = 'linepointer' + id;
		this.linkMenu('linepointer' + id);
		return group;
		
		
	},
	add: function(id, pathsId, nodeIdFrom, nodeIdTo, cost) {
		var self = this;
		if (this.moves[nodeIdFrom] == undefined) {
			console.warn('No existe nodo origen con id ' + nodeIdFrom
					+ ' para enlace id +' + id);
			return false;
		}
		
		if (this.moves[nodeIdTo] == undefined) {
			console.warn('No existe nodo destino con id ' + nodeIdTo
					+ ' para enlace id ' + id);
			return false;
		}
		
		this.linesGroups[id] = this.surface.createGroup();
		var x1 = this.moves[nodeIdFrom].shape.matrix.dx;
		var y1 = this.moves[nodeIdFrom].shape.matrix.dy;
		var x2 = this.moves[nodeIdTo].shape.matrix.dx;
		var y2 = this.moves[nodeIdTo].shape.matrix.dy;
		var dx = 0;
		var dy = 0;
		var ev;
		var scale = 1;
		var curveType = "S";

		if (globalOpc != 'clone' && typeof (this.lines[parseInt(id)]) != 'undefined') {
			// Si estamos editando linea existente, remover linea anterior y eventos
			// de movimiento asociados a nodos
			this.lines[parseInt(id)].removeShape();
			this.linesHelpers[parseInt(id)].removeShape();
			this.linesGroups[parseInt(id)].removeShape();
			dojo.forEach(this.events[id], function(e) {
				dojo.disconnect(e);
			});
		} else {
			this.events[id] = [];
		}

		path = this.getConnectorPath(x1, y1, x2, y2, dx, dy, curveType);

		this.lines[id] = this.surface.createPath(path).setStroke({
			style : "Solid",
			width : 9,
			color : "#507869",
			'marker-end' : 'url(#head)'
		});

		this.linesHelpers[id] = this.drawPointer(x1, y1, x2, y2, id, cost);

		this.lines[id].cost = cost;
		this.lines[id].nodeIdFrom = nodeIdFrom;
		this.lines[id].nodeIdTo = nodeIdTo;
		this.lines[id].rawNode.setAttribute('nodesidfrom', nodeIdFrom);
		this.lines[id].rawNode.setAttribute('nodesidto', nodeIdTo);
		this.lines[id].rawNode.setAttribute('cost', cost);

		this.lines[id].rawNode.style.cursor = 'pointer';
		this.lines[id].rawNode.id = 'link' + id;
		this.linkMenu('link' + id);
		this.linesGroups[id].add(this.lines[id]);
		this.linesGroups[id].add(this.linesHelpers[id]);
		this.group.add(this.linesGroups[id]);
		this.linesGroups[id].moveToBack();
		this.lines[id].moveToBack();
		this.linesHelpers[id].moveToBack(); 
		

		if (typeof (this.moves[nodeIdFrom]) != 'undefined') {
			ev = dojo.connect(this.moves[nodeIdFrom], "onMoved", function(mover, shift) {
				if (typeof (self.lines[id]) != 'undefined') {
					if (typeof (dojo.cookie("scale")) != 'undefined')
						scale = parseFloat(dojo.cookie("scale"));

					dx = shift.dx / scale;
					dy = shift.dy / scale;

					x1 = self.moves[nodeIdFrom].shape.matrix.dx;
					y1 = self.moves[nodeIdFrom].shape.matrix.dy;

					x2 = self.moves[nodeIdTo].shape.matrix.dx;
					y2 = self.moves[nodeIdTo].shape.matrix.dy;

					path = self.getConnectorPath(x1, y1, x2, y2, dx, dy, curveType);
					self.lines[id].setShape(path);
					self.moveArrowHelpers(x1, y1, x2, y2, id, self.linesHelpers[id]);
				}
			});
			this.events[id].push(ev);
		}

		if (typeof (this.moves[nodeIdTo]) != 'undefined') {
			ev = dojo.connect(this.moves[nodeIdTo], "onMoved", function(mover, shift) {
				if (typeof (self.lines[id]) != 'undefined') {
					if (typeof (dojo.cookie("scale")) != 'undefined')
						scale = parseFloat(dojo.cookie("scale"));
					dx = shift.dx / scale;
					dy = shift.dy / scale;

					x1 = self.moves[nodeIdFrom].shape.matrix.dx;
					y1 = self.moves[nodeIdFrom].shape.matrix.dy;

					x2 = self.moves[nodeIdTo].shape.matrix.dx;
					y2 = self.moves[nodeIdTo].shape.matrix.dy;

					var path = self.getConnectorPath(x1, y1, x2, y2, dx, dy, curveType);
					self.lines[id].setShape(path);
					self.moveArrowHelpers(x1, y1, x2, y2, id, self.linesHelpers[id]);
				}
			});
			this.events[id].push(ev);
		}

		this.lines[id].connect("ondblclick", function(e) {
			var form = new zwei.Form({
				component : 'enlaces.xml',
				ajax : true,
				action : 'edit',
				queryParams : 'servicios_id=' + self.id,
				primary : {
					'id' : id
				},
				dijitDialog : dijit.byId('enlaces_xmldialog_edit')
			});
			form.showDialog();
		});
	}
	
});