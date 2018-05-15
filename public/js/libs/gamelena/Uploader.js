dojo.declare("gamelena.Uploader", dojo.Stateful, {
	dijitFormSearch : null,
	dijitDataGrid : null,
	uploadsControllerAction : null,
	truncate: '',
	url : null,
	queryKeys: '',
	component : null,
	onShow: function(){console.log('on show');},
	onHide: function(){console.log('on hide');},
	iframe : dojo.byId('ifrm_process'),
	constructor : function(args) {
		dojo.mixin(this, args);
	},
	showDialog : function(accion) {
		dojo.require('dojox.widget.DialogSimple');
		dojo.require('dojox.form.Uploader');
		dojo.require('dijit.ProgressBar');
		
		if (this.dijitFormSearch) {
			var keys = [];
			for (var index in this.dijitFormSearch.get('value')) {
				console.log(index, this.dijitFormSearch.get('value')[index]);
				keys.push('keys[' + index + ']=' + this.dijitFormSearch.get('value')[index]);
			}
			this.queryKeys += keys.join('&');
		}

		var url = this.url ? this.url : base_url + '/components/dojo-simple-crud/upload-form';

		var href = url + '?'
				+ this.queryKeys + '&accion=' + accion + '&jsUploaderInstance='
				+ this.jsUploaderInstance + "&p=" + this.component + "&truncate=" + this.truncate;
		
		if (this.path) {
			href += '&path=' + this.path;
		}
		
		if (!dijit.byId('fileUploaderDlg')) {
			var myDialog = new dojox.widget.DialogSimple({
				id : 'fileUploaderDlg',
				title : 'Cargar Archivo',
				executeScripts : true,
				href : href,
				onShow: this.onShow,
				onHide: this.onHide
			});
		} else {
			dijit.byId('fileUploaderDlg').set('href', href);
		}
		var self = this;
		dijit.byId('fileUploaderDlg').show();
	}
});