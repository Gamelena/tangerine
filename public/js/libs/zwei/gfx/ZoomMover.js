define(["dojo/has", "dojo/_base/event"],
    function(has, event){

    dojo.declare("zwei.gfx.ZoomMover", dojox.gfx.Mover, {
            // mouse event processors
            onMouseMove: function(e){
                // summary:
                //      event processor for onmousemove
                // e: Event
                //      mouse event
                var x = has("touch") ? (e.changedTouches ? e.changedTouches[0] : e).clientX : e.clientX;
                var y = has("touch") ? (e.changedTouches ? e.changedTouches[0] : e).clientY : e.clientY;
                
                scale = (typeof(dojo.cookie("scale")) != 'undefined') ? parseFloat(dojo.cookie("scale")) : 1;
                //console.debug(this.shape);
                this.myCurrX = Math.round(this.myCurrX + (x - this.lastX) / scale); 
                this.myCurrY = Math.round(this.myCurrY + (y - this.lastY) / scale);
                
                this.shape.setTransform( dojox.gfx.matrix.translate(this.myCurrX, this.myCurrY));
                this.inherited(arguments);
            },
            // utilities
            onFirstMove: function(){
                this.inherited(arguments);
                this.myCurrX=this.host.shape.matrix.dx;
                this.myCurrY=this.host.shape.matrix.dy;
            }
        });
});
