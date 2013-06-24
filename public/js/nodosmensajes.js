dojo.require("dojo.cookie");
dojo.require("dojox.gfx");
dojo.require("dojox.gfx.move");
dojo.require("dojox.gfx.fx");
dojo.require("dijit.form.VerticalSlider");
dojo.require("dijit.form.VerticalRule");
dojo.require("dijit.form.VerticalRuleLabels");
dojo.require("dijit.form.VerticalRuleLabels");
dojo.require("zwei.gfx.ZoomMover");

var globalNodeType;
var shapeCircleX=0;
var shapeCircleY=0;
var posGroupX=0;
var posGroupY=0;
var posMoveX=0;
var posMoveY=0;
var globalNodeId=0;


var container = null,
surface = null,
surface_size = null;

var circles = [];
var circlesLabels = [];
var moves = [];
var groups = [];
var lines = [];

var collision;

var initGfx = function(domPrefix){
    if (typeof(domPrefix) == "undefined") var domPrefix = "";
    
    container = dojo.byId(domPrefix + "gfx_holder");
    surface = dojox.gfx.createSurface(container, 4800, 2400);
    
    //surface_size = {width: 1600, height: 1200};

    //dojo.connect(container, "ondragstart",   dojo, "stopEvent");
    //dojo.connect(container, "onselectstart", dojo, "stopEvent");
};


var zoomCanvas = function(scale, campanaId, id){
    console.log(scale);
    dojo.cookie("scale", scale, { expires: 5 });
    campanaGroups[campanaId].setTransform( dojox.gfx.matrix.scale( scale, scale));
    /*
    if (scale <= 0.9) {
        var myLeft = Math.round(40 *(1/scale));
        var myTop = Math.round(40 *(1/scale));
        dojo.byId(id).style.left=myLeft+'px';
        dojo.byId(id).style.top=myTop+'px';
    } else {
        dojo.byId(id).style.left='0px';
        dojo.byId(id).style.top='0px';
    }
    */
};


var changeNodeName = function(id)
{
    //prompt("Ingrese nuevo nombre para nodo " + id);
};

var log = function(msg) {
    var elt = document.createTextNode(msg);
    var div = dojo.byId('log');
    div.insertBefore(elt, div.firstChild);
    div.insertBefore(document.createElement('br'), elt.nextSibling);
};

var addMenu = function(id){
    var pMenu;

    var nodeId = id.replace( /^\D+/g, '');
    pMenu = new dijit.Menu({
        targetNodeIds: [id]
    });
    pMenu.addChild(new dijit.MenuItem({
        label: "Editar Nodo",
        iconClass: "dijitIconEdit",
        onClick: function() {
            groups[nodeId].moveToFront();
            var form = new zwei.Form({
                component: 'mensajes.xml',
                ajax: true,
                action: 'edit',
                primary: {'id': nodeId},
                dijitDialog: dijit.byId('mensajes_xmldialog_edit')
            }); 
            form.showDialog();
            //cargarTabsPanelCentral('mensajes.xml', 'edit', 'id', 'mensajes_xml', nodeId);
        }
    }));
    pMenu.addChild(new dijit.MenuItem({
        label: "Eliminar Nodo",
        iconClass: "dijitIconDelete",
        onClick: function(){
            if (confirm('Borrar nodo '+nodeId)) {
                deleteNode(nodeId, 'TreeNodesModel', 'nodes_id', 'Nodo');
            }
        }
    }));
    pMenu.addChild(new dijit.MenuItem({
        label: "Clonar Nodo",
        iconClass: "dijitIconCopy",
        onClick: function(){
            groups[nodeId].moveToFront();
            var form = new zwei.Form({
                component: 'mensajes.xml',
                ajax: true,
                action: 'clone',
                primary: {'id': nodeId},
                dijitDialog: dijit.byId('mensajes_xmldialog_add')
            }); 
            form.showDialog();
        }
    }));
    pMenu.addChild(new dijit.MenuSeparator());
    
    pMenu.addChild(new dijit.MenuItem({
        label: "Enlaces",
        iconClass: "dijitIconConnector",
        onClick: function(){
            groups[nodeId].moveToFront();
            var form = new zwei.Form({
                component: 'enlaces.xml',
                ajax: true,
                action: 'add',
                queryParams: 'group_id=' + campanaId + '&from_id='+nodeId,
                primary: {'id': nodeId},
                dijitDialog: dijit.byId('enlaces_xmldialog_add')
            }); 
            form.showDialog();
            //cargarTabsPanelCentral('enlaces.xml&group_id='+campanaId+'&from_id='+nodeId, 'add', 'id', 'enlaces_xml', nodeId);
        }
    }));
    

    /*
    var pSubMenu = new dijit.Menu();
    pSubMenu.addChild(new dijit.MenuItem({
        label: "Origen"
    }));
    pSubMenu.addChild(new dijit.MenuItem({
        label: "Destino"
    }));
    pMenu.addChild(new dijit.PopupMenuItem({
        label: "Máquinas de Respaldo",
        popup: pSubMenu
    }));
    */
    pMenu.startup();
};


var linkMenu = function(id){
    var pMenu;

    var nodeId = id.replace( /^\D+/g, '');
    pMenu = new dijit.Menu({
        targetNodeIds: [id]
    });
    pMenu.addChild(new dijit.MenuItem({
        label: "Editar Enlace",
        iconClass: "dijitIconEdit",
        onClick: function() {
            var form = new zwei.Form({
                component: 'enlaces.xml',
                ajax: true,
                action: 'edit',
                queryParams: 'group_id=' + campanaId,
                primary: {'id': nodeId},
                dijitDialog: dijit.byId('enlaces_xmldialog_edit')
            }); 
            form.showDialog();
        }
    }));
    pMenu.addChild(new dijit.MenuItem({
        label: "Eliminar Enlace",
        iconClass: "dijitIconDelete",
        onClick: function(){
            if (confirm('Borrar enlace '+nodeId)) {
                deleteNode(nodeId, 'TreeLinksModel', 'id', 'Enlace');
            }
        }
    }));
    pMenu.startup();
};





var saveCanvas = function(){
    var children = surface.children;
    console.debug(children);
    
    dojo.forEach(children, function(child)
    {

    });
};



var listenCirclesCollisions = function(mover, shift) {
    console.debug(mover);
};

var addLink = function(response, campanaId) {
    console.debug(response);
    var id = typeof(response.more.lastInsertedId) != 'undefined' ? response.more.lastInsertedId : response.more.where.id;
    
    console.debug(id);
    console.debug(lines[parseInt(id)]);
    console.debug(lines);
    
    addLine(id, campanaId, response.more.data.from_id, response.more.data.to_id);
};


var getConnectorPath = function(x1, y1, x2, y2, dx, dy, curveType) {
    p = [{x: x1 + Math.abs(x1-x2)/ 2, y: y1 - 1},
         {x: x1 + Math.abs(x1-x2) / 2, y: y1 + Math.abs(y1-y2) + 1},
         {x: x1 - 1, y: y1 + Math.abs(y1-y2) / 2},
         {x: x1 + Math.abs(x1-x2) + 1, y: y1 + Math.abs(y1-y2) / 2},
         {x: x2 + Math.abs(x1-x2) / 2, y: y2 - 1},
         {x: x2 + Math.abs(x1-x2) / 2, y: y2 + Math.abs(y1-y2) + 1},
         {x: x2 - 1, y: y2 + Math.abs(y1-y2) / 2},
         {x: x2 + Math.abs(x1-x2) + 1, y: y2 + Math.abs(y1-y2) / 2}],
         d = {}, dis = [];
    
    for (var i = 0; i < 4; i++) {
        for (var j = 4; j < 8; j++) {
            if ((i == j - 4) || (((i != 3 && j != 6) || p[i].x < p[j].x) && ((i != 2 && j != 7) || p[i].x > p[j].x) && ((i != 0 && j != 5) || p[i].y > p[j].y) && ((i != 1 && j != 4) || p[i].y < p[j].y))) {
                dis.push(dx + dy);
                d[dis[dis.length - 1]] = [i, j];
            }
        }
    }
    
    if (dis.length == 0) {
        var res = [0, 4];
    } else {
        res = d[Math.min.apply(Math, dis)];
    }

    console.debug(res);
    /*
    x1 = p[res[0]].x,
    y1 = p[res[0]].y,
    x2 = p[res[1]].x,
    y2 = p[res[1]].y;
    */
    
    dx = Math.max(Math.abs(x1 - x2) / 2, 10);
    dy = Math.max(Math.abs(y1 - y2) / 2, 10);
    xa = [x1, x1, x1 - dx, x1 + dx][res[0]].toFixed(3);
    ya = [y1 - dy, y1 + dy, y1, y1][res[0]].toFixed(3);
    xb = [0, 0, 0, 0, x2, x2, x2 - dx, x2 + dx][res[1]].toFixed(3);
    yb = [0, 0, 0, 0, y1 + dy, y1 - dy, y2, y2][res[1]].toFixed(3);
    //return "M"+ x1.toFixed(3) + "," + y1.toFixed(3) + curveType + x1 +","+ ya+" "+xb+","+y2+" "+x2.toFixed(3) + "," + y2.toFixed(3);
    
    var path = ["M", x1.toFixed(3), y1.toFixed(3), curveType, xa, ya, xb, yb, x2.toFixed(3), y2.toFixed(3)].join(",");
    return path;
};


var addLine = function(id, campanaId, nodeIdFrom, nodeIdTo) {
    var x1 = moves[nodeIdFrom].shape.matrix.dx;
    var y1 = moves[nodeIdFrom].shape.matrix.dy;
    var x2 = moves[nodeIdTo].shape.matrix.dx;
    var y2 = moves[nodeIdTo].shape.matrix.dy;
    var dx = 0;
    var dy = 0;
    
    var scale = 1;
    var curveType = "C";
    
    if (globalOpc != 'clone' && typeof(lines[parseInt(id)]) != 'undefined') {
        console.debug(lines[id]);
        lines[parseInt(id)].removeShape();
    } else {
        //console.debug(lines[id]);
        
    }
    
    
    path = getConnectorPath(x1, y1, x2, y2, dx, dy, curveType);
    
    lines[id] = surface.createPath(path).setStroke(
        {style: "Solid", width: 9, cap: "round", join: "mitter", color:"#507869"}
    );
    
    lines[id].x1 = x1;
    lines[id].y1 = y1;
    
    lines[id].x2 = x2;
    lines[id].y2 = y2;
    
    lines[id].rawNode.style.cursor = 'pointer';
    lines[id].rawNode.id = 'link'+id;
    linkMenu('link'+id);
    
    console.debug(campanaId);
    campanaGroups[campanaId].add(lines[id]);
    lines[id].moveToBack();
    
    lines[id].connect("ondblclick", function(e){
        var form = new zwei.Form({
            component: 'enlaces.xml',
            ajax: true,
            action: 'edit',
            queryParams: 'group_id=' + campanaId,
            primary: {'id': id},
            dijitDialog: dijit.byId('enlaces_xmldialog_edit')
        }); 
        form.showDialog();
        //cargarTabsPanelCentral('enlaces.xml&group_id='+campanaId, 'edit', 'id', 'enlaces_xml', id);
    });
    
    if (typeof(moves[nodeIdFrom]) != 'undefined') {
        dojo.connect(moves[nodeIdFrom], "onMoved", function(mover, shift){
            if (typeof(lines[id]) != 'undefined') { 
                if (typeof(dojo.cookie("scale")) != 'undefined') scale = parseFloat(dojo.cookie("scale"));
                box = lines[id].getBoundingBox();
                
                dx = shift.dx/scale;
                dy = shift.dy/scale;
                console.debug(dx);
                x1 = lines[id].x1 + dx;
                y1 = lines[id].y1 + dy;
                
                x2 = lines[id].x2 ;
                y2 = lines[id].y2 ;
                
                path = getConnectorPath(x1, y1, x2, y2, dx, dy, curveType);
                console.debug('nodeIdFrom:'+path);
                lines[id].setShape(path);
                lines[id].x1 = x1;
                lines[id].y1 = y1;
                lines[id].x2 = x2;
                lines[id].y2 = y2;
            }
        });
    }
    
    if (typeof(moves[nodeIdTo]) != 'undefined') {
        dojo.connect(moves[nodeIdTo], "onMoved", function(mover, shift){
            //last = this.last;
            if (typeof(lines[id]) != 'undefined') { 
                if (typeof(dojo.cookie("scale")) != 'undefined') scale = parseFloat(dojo.cookie("scale"));
                box = lines[id].getBoundingBox();

                dx = shift.dx/scale;
                dy = shift.dy/scale;

                x1 = lines[id].x1;
                y1 = lines[id].y1;
                
                x2 = lines[id].x2 + dx ;
                y2 = lines[id].y2 + dy;
                
                path = getConnectorPath(x1, y1, x2, y2, dx, dy, curveType);
                console.debug('nodeIdFrom:'+path);
                lines[id].setShape(path);
                lines[id].x1 = x1;
                lines[id].y1 = y1;
                lines[id].x2 = x2;
                lines[id].y2 = y2;
            }
        });
    }
};



var updateLink = function(id, fromId, toId) {
}



var addNode = function(response, campanaId, id, x1, y1, rebuild) {
    var nombre = response.more.data.nombre;
    if (typeof(rebuild) == 'undefined') var rebuild = false;
    
    if (globalOpc == 'add' || rebuild) {
        this.x1 = (typeof(x1) == 'undefined') ? 100 : x1;
        this.y1 = (typeof(y1) == 'undefined') ? 100 : y1;
        if (typeof(globalNodeType) == 'undefined') var globalNodeType = 1; 
        
        var nodeId = typeof(id) == 'undefined' || id == null ? response.more.lastInsertedId : id;
        
        console.log(campanaId);
        groups[nodeId] = surface.createGroup();
        campanaGroups[campanaId].add(groups[nodeId]);
        
        // our geometry
        this.r = 50;
        
        if (nombre != null) {
            if (globalNodeType == 1) {
                circles[nodeId] = surface.createCircle({cx: 0, cy: 0, r: this.r}).setFill({
                    type: "radial",
                    cx: 0,
                    cy: 0,
                    colors: [
                        { offset: 0,   color: "rgb(162, 253, 0)" },
                        { offset: 1,   color: "#096301" }
                    ]
                });
            } else {
                circles[nodeId] = surface.createCircle({cx: 0, cy: 0, r: this.r}).setFill({
                    type: "radial",
                    cx: 0,
                    cy: 0,
                    colors: [
                        { offset: 0,   color: "#f3001f" },
                        { offset: 1,   color: "#a40016" }
                    ]
                });
            }
            
            circlesLabels[nodeId] = surface.createText({ x:-40, y:5, text: utils.htmlEntityDecode(nombre), align:"center"}).setFont({ family:"Arial", size:"12pt", weight:"bold" }).setFill("#ffffff");
            circlesLabels[nodeId].rawNode.id = 'label'+nodeId;
            circlesLabels[nodeId].rawNode.style.cursor = 'pointer'; 
            circlesLabels[nodeId].connect("ondblclick", function(e){
                var form = new zwei.Form({
                    component: 'mensajes.xml',
                    ajax: true,
                    action: 'edit',
                    primary: {'id': nodeId},
                    dijitDialog: dijit.byId('mensajes_xmldialog_edit'), 
                    dijitForm: dijit.byId('mensajes_xmlform_edit')
                }); 
                form.showDialog();
                //cargarTabsPanelCentral('mensajes.xml', 'edit', 'id', 'mensajes_xml', nodeId);
                groups[nodeId].moveToFront();
            });
            
            
            groups[nodeId].add(circles[nodeId]);
            groups[nodeId].add(circlesLabels[nodeId]);
            groups[nodeId].setTransform(dojox.gfx.matrix.translate(this.x1, this.y1));
            moves[nodeId] = new dojox.gfx.Moveable(groups[nodeId], { mover: zwei.gfx.ZoomMover });
            
            dojo.connect(moves[nodeId], "onMoveStart", function(mover, shift){
                groups[nodeId].moveToFront();
                globalNodeId = nodeId;
            });
            
            
            dojo.connect(moves[nodeId], "onMoveStop", function(mover, shift){
                if (groups[nodeId].matrix != null) {
                    saveNode(nodeId, response);
                }
            });
            
            
    
            circles[nodeId].connect("ondblclick", function(e){
                var form = new zwei.Form({
                    component: 'mensajes.xml',
                    ajax: true,
                    action: 'edit',
                    primary: {'id': nodeId},
                    dijitDialog: dijit.byId('mensajes_xmldialog_edit')
                }); 
                form.showDialog();
                //cargarTabsPanelCentral('mensajes.xml', 'edit', 'id', 'mensajes_xml', nodeId);
                groups[nodeId].moveToFront();
            });
            
            circles[nodeId].rawNode.id = 'circle'+nodeId;
            circles[nodeId].rawNode.style.cursor = 'pointer'; 
            addMenu('circle'+nodeId);
            addMenu('label'+nodeId);
        }
    } else {
        id = parseInt(response.more.where.id);
        console.debug(rebuild);
        updateNode(id, nombre);
    }
};

var saveNode = function(nodeId, response) {
    var myX = groups[nodeId].matrix.dx;
    var myY = groups[nodeId].matrix.dy;
    dojo.xhrPost({
        url: base_url+'crud-request',
        content: {
            'data[x]'   : myX,
            'data[y]'   : myY,
            'primary[nodes_id]'  : nodeId,
            'action'    :'edit',
            'model'     : 'TreeNodesModel',
            'format'    : 'json'
        },
        handleAs: 'json',
        sync: true,
        preventCache: true,
        timeout: 5000,
        load: function(respuesta) {
            dijit.byId('firstToaster').setContent('<b/>Posición Actualizada <br/> nodo ' + nodeId + '<br/>'+response.more.data.nombre+"</b> ("+myX+","+myY+") ", 'message');
            dijit.byId('firstToaster').show();
        },
        error:function(err) {
            console.debug(err);
            dijit.byId('firstToaster').setContent('Error en comunicación de datos', 'fatal');
            dijit.byId('firstToaster').show();
        }
    });
}


var deleteNode = function(nodeId, model, id, alias) {
    if (typeof(alias) == 'undefined') var alias = 'Elemento';
    
    myContent = {};
    myContent['primary['+id+']'] = nodeId;
    myContent['action'] = 'delete'; 
    myContent['model'] = model; 
    myContent['format'] = 'json'; 
    
    dojo.xhrPost( {
        url: base_url+'crud-request',
        content: myContent,
        handleAs: 'json',
        sync: true,
        preventCache: true,
        timeout: 5000,
        load: function(respuesta) {
            dijit.byId('firstToaster').setContent('<b/>'+alias+' ' + nodeId + ' Eliminado ', 'message');
            dijit.byId('firstToaster').show();
            if (model == 'TreeNodesModel') {
                circlesLabels[nodeId].removeShape();
                circles[nodeId].removeShape();
                groups[nodeId].destroy();
                moves[nodeId].destroy();
            } else if (model == 'TreeLinksModel') {
                lines[nodeId].removeShape();
            }
        },
        error:function(err) {
            console.debug(err);
            dijit.byId('firstToaster').setContent('Error en comunicación de datos', 'fatal');
            dijit.byId('firstToaster').show();
        }
    });
};

var updateNode = function (idNode, nombre) {
    console.debug(idNode);
    this.x = circlesLabels[idNode].shape.x;
    this.y = circlesLabels[idNode].shape.y;
    circlesLabels[idNode].removeShape();
    circlesLabels[idNode] = surface.createText({ x:this.x, y:this.y, text: utils.htmlEntityDecode(nombre), align:"center"}).setFont({ family:"Arial", size:"12pt", weight:"bold" }).setFill("#ffffff");
    groups[idNode].add(circlesLabels[idNode]);
    groups[idNode].moveToFront();
}

var highLightNode = function(idNode) {
    new dojox.gfx.fx.animateStroke({
        duration: 2400,
        shape: circles[idNode],
        color: {start: "green", end: "yellow"},
        width: {end: 15, start:15},
        join:  {values: ["outer", "bevel", "round"]},
        onAnimate: function() {
            //onAnimate
        },
        onEnd: function() {
            new dojox.gfx.fx.animateStroke({
                duration: 1200,
                shape: circles[idNode],
                color: {start: "yellow", end: "green"},
                width: {end: 1},
                join:  {values: ["round", "bevel", ""]},
                onEnd: function() {
                    //onEnd
                }
            }).play();
        }
    }).play();
};

