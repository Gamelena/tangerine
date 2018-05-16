dojo.provide("dijit.DateTimeWidget");
dojo.require("dojo.fx");
dojo.require("dojo.cldr.supplemental");
dojo.require("dojo.date");
dojo.require("dojo.date.locale");
dojo.require("dijit._Widget");
dojo.require("dijit._Calendar");
dojo.require("dijit.form.TimeTextBox");
dojo.require("dijit._TemplatedMixin");

dojo.declare("gamelena.form.DateTimeTextBox", [ dijit._Widget, dijit._TemplatedMixin ],

{

	widgetsInTemplate : true,
	templateString : "<div class=\"tundra\" style=\"width: 200px; background-color: white;\"><input dojoAttachEvent=\"onclick:_onImageClick,onkeyup:_onImageClick\" dojoAttachPoint=\"myTextBox\"></input> <div	style=\"display: none; width: 182px; border-style: solid; background-color: white; border-width: 1px; border-color: olive;\" dojoAttachPoint=\"myCalendarTime\" target=\"${target}\"> <div dojoType=\"dojox/widget/DailyCalendar\" dojoAttachPoint=\"myCalendar\" 	id=\"myCalendar_${id}\" style=\"_height: 161px\"></div> <div style=\"text-align: left; width: 182px; margin-top: 2px; background-color: white;\" dojoAttachPoint=\"myButtonTime\"> <div style=\"float: left;\"><strong>Time</strong> <input type=\"text\" 	name=\"mytime_${id}\" id=\"mytime_${id}\" dojoType=\"dijit.form.TimeTextBox\" required=\"true\" constraints=\" {timePattern: 'hh:mm:ss a', clickableIncrement: 'T00:01:00', visibleIncrement: 'T00:01:00', visibleRange: 'T01:00:00' }\" style=\"width: 110px\" dojoAttachPoint=\"myTime\" value=\"${time}\" /></div> <button id=\"myButton_${id}\" dojoAttachEvent=\"onclick:onMouseOut\" 	style=\"background-color: #FFE4CF; border: 1px #FFE4CF solid; outline-color: #888888; width: 20px; font-size: 12px; margin-top: 5px;\">Ok</button> </div> </div> </div>", 
	target : "",
	time : new Date(),
	constructor : function() {
	
	},
	postMixInProperties : function() {

	},
	postCreate : function() {
	
	var controller = this;
	dojo.attr(controller.myTime, "value", new Date());
	
},

_onImageClick : function(e) {

	

	dojo.attr(this.myCalendar, "value", new Date(this.myTextBox.value));
	dojo.attr(this.myTime, "value", new Date(this.myTextBox.value));
	this.myTextBox.style.borderColor='orange';
	this.myCalendarTime.style.display = 'block';
	this.myCalendarTime.style.position='absolute';
	this.myCalendarTime.style.zIndex = 999;   //ensure it is on top when it pops up

	    var coord = dojo.coords(this.myTextBox);
	    this.myCalendarTime.style.left = coord.x  + 'px';
	    this.myCalendarTime.style.top = coord.y +(coord.h)+ 'px';
//	moveIt(this.myCalendarTime.id,this.myTextBox.id);

},

getDateTime : function() {
	return dojo.date.locale.format(this.myCalendar.value, {
		datePattern : 'M/dd/yyyy',
		selector : 'date'
	}) + " " + dojo.date.locale.format(this.myTime.value, {
		timePattern : 'hh:mm:ss a',
		selector : 'time'
	});
},
setDateTime : function(e) {
 this.myTextBox.value=	e;
},

onMouseOut : function(e) {
	this.target = dojo.byId(dojo.attr(this.myCalendarTime, "target"));
		
	this.target = dojo.byId(dojo.attr(this.myCalendarTime, "target"));
	this.target.value = dojo.date.locale.format(this.myCalendar.value, {
		datePattern : 'M/dd/yyyy',
		selector : 'date'
	}) + " " + dojo.date.locale.format(this.myTime.value, {
		timePattern : 'hh:mm:ss a',
		selector : 'time'
	});
	this.myTextBox.style.borderColor='';
	this.myTextBox.value=this.target.value;
	this.myCalendarTime.style.display='none';
	e.preventDefault();
}

});