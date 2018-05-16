
define(["dojo/_base/lang", "dojox/gfx/_base", "dojox/gfx/renderer!"], 
  function(lang, gfxBase, renderer){
    // module:
    //      dojox/gfx
    // summary:
    //      This the root of the Dojo Graphics package
    gfxBase.switchTo(renderer);
    
    //Extensiones para GFX
    dojo.extend(dojox.gfx.shape.Surface, {
        createMoveableNode: function(shape, fill, text) {
            var g = this.createGroup();
            var c = this.createCircle(shape);
            if (fill) c.setFill(fill);
            var t = this.createLabel(g, text);
            g.add(c);
            g.add(t);
            var m = new dojox.gfx.Moveable(g, { mover: gamelena.gfx.ZoomMover });
            
            //dojo.mixin(g, foo.MyOwnGroup);
            return {movable: m, shape: c, group: g, label: t };
        },
        createLabel: function(group, text) {
            var label = group.createText({ x:-40, y:5, text: utils.htmlEntityDecode(text), align:"center"})
                .setFont({ family:"Arial", size:"12pt", weight:"bold" })
                .setFill("#ffffff");
            return label;
        },
        getConnectorPath: function(x1, y1, x2, y2, dx, dy, curveType) {
            var alpha = Math.atan(Math.abs(y1-y2)/Math.abs(x1-x2));
            var dx2 = radio * Math.cos(alpha);
            var dy2 = radio * Math.sin(alpha);
            
            var beta = 90 - alpha;
            var dx1 = radio * Math.sin(beta);
            var dy1 = radio * Math.cos(beta);

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
            
            var path = ["M", x1, y1, x2, y2].join(",");
            return path;
        }
    });
    return gfxBase;
});

