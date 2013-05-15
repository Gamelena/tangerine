dojo.declare("zwei.Utils", null, {
    constructor: function(args){
        dojo.mixin(this, args);
    },
    showMessage: function(message, type, duration, direction, myToaster) {
        if (typeof(type) == 'undefined') var type = 'message';
        if (typeof(duration) == 'undefined') var duration = '2000';
        if (typeof(direction) == 'undefined') var direction = 'br-left';
        if (typeof(myDijit) == 'undefined') var myToaster = dijit.byId('firstToaster');
        
        myToaster.positionDirection = direction;
        myToaster.setContent(message, type, duration);
        myToaster.show();
    },
    getIframeContent: function(iframe) {
        
    }
});
