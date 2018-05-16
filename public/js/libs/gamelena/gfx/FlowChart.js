dojo.declare("gamelena.gfx.FlowChart", null, {
	id: null,
	surface: null,
	domPrefix: null,
	group: null,
	circles: [],
	circlesLabels: [],
	moves: [],
	groups: [],
	lines: [],
	linesGroups: [],
	linesHelpers: [],
	linesPointers: [],
	linesLabels: [],
	events: [],
	radio: 50,
	nodeComponent: null,
	connectorComponent: null,
	constructor: function(args) {
		dojo.declare.safeMixin(this, args);
	},
	init : function() {
		var self = this;
		require(['gamelena/gfx'], function(gfx){
			console.debug(self.domPrefix + "gfx_holder");
			var container = dojo.byId(self.domPrefix + "gfx_holder");
			self.surface = gfx.createSurface(container, 4800, 2400);
			
			dojo.connect(self.surface, "ondragstart", dojo, function(e) {
				console.debug(e)
			});
			dojo.connect(self.surface, "onselectstart", dojo, function(e) {
				console.debug(e)
			});	
		});
		this.group = this.surface.createGroup();
	},
	addNode: function(data, x1, y1) {
		var self = this;
		require(['gamelena/gfx/chart/Node'], function(Node){
			var label = data[self.labelName];
			var node = new Node({
				component: self.nodeComponent,
			});
			node.add(data, x1, x2);
		}) 
	},
	addConnector: function (originShape, destinyShape, data)
	{
		var self = this;
		require(['gamelena/gfx/chart/Connector'], function(Connector){
			var connector = new Connector(
			);
			connector.add()
		});
	},
	highLightShape: function(myShape) {
		var self = this;
		require(['dojox/gfx/fx'], function(fx){
			myShape.moveToFront();
			var stroke = myShape.getStroke();
			var color = stroke != null ? stroke.color : 'green';
			var width = stroke != null ? stroke.width : 1;

			self.animation = new fx.animateStroke({
				duration : 2400,
				shape : myShape,
				color : {
					start : "#FFA600",
					end : "yellow"
				},
				width : {
					end : 60,
					start : 60
				},
				join : {
					values : [ "outer", "bevel", "radial" ]
				},
				onAnimate : function() {
					// onAnimate
					myShape.moveToFront();
				},
				onEnd : function() {
					myShape.moveToFront();
					new fx.animateStroke({
						duration : 1200,
						shape : myShape,
						color : {
							end : color
						},
						width : {
							end : width
						}
					}).play();
					myShape.moveToFront();
				}
			});
			self.animation.play();
			myShape.moveToFront();
		});
	}
});