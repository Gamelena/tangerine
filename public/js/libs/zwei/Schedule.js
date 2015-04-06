dojo.declare("zwei.Schedule", null, {
	dijitDataGrid: null,
	domPrefix: null,
	id: null,
	primary: {},
	constructor : function(args) {
		dojo.declare.safeMixin(this, args);
		dojo.cookie('calendarReload', '1', {expires: 5});
	},
	formatHolidays : function(value) {
		if (value == 'todos-menos-feriados') {
			return 'No incluir feriados.';
		} if (value == 'solo-feriados') {
			return 'Incluir sólo feriados.';
		} if (value == 'todo') {
			return 'Incluir todos los días marcados.';
		}
		return value;
	},
	formatWeekdays : function (value) {
		var weekdays = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
		var selected = [];
		for (var i = 0; i < weekdays.length; i++) {
 			if ((value & Math.pow(2, i)) > 0) {
				selected.push(weekdays[i]);
			}
		}
		return selected.join(", ");
		
	},
	loadCalendar : function () {
		console.log('loadCalendar');
		if (dojo.cookie('calendarReload')) { 
			var primaries = '';
			for (var key in this.primary) {
				primaries += 'primary[' + key + ']=' + this.primary[key];
			}
			
			dijit.byId(this.domPrefix +'Pane0').set('href', base_url + 'components/schedule?' + primaries + '&model=' + this.model); 
			dojo.cookie('calendarReload', '0', {expires: 0});
		}
	}
	
	
});