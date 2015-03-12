dojo.declare("zwei.Uploader", dojo.Stateful, {
	dijitFormSearch : null,
	dijitDataGrid : null,
	truncate: '',
	component : null,
	iframe : dojo.byId('ifrm_process'),
	constructor : function(args) {
		dojo.mixin(this, args);
	},
	showDialog : function(accion) {
		dojo.require('dojox.widget.DialogSimple');
		dojo.require('dojox.form.Uploader');
		dojo.require('dijit.ProgressBar');
		this.dijitFormSearch = dijit.byId(this.domPrefix + 'formSearch');

		var href = base_url + '/components/dojo-simple-crud/upload-form?'
				+ this.queryKeys + '&accion=' + accion + '&jsUploaderInstance='
				+ this.jsUploaderInstance + "&p=" + this.component + "&truncate=" + this.truncate;

		if (!dijit.byId('fileUploaderDlg')) {
			var myDialog = new dojox.widget.DialogSimple({
				id : 'fileUploaderDlg',
				title : 'Cargar Archivo',
				executeScripts : true,
				href : href
			});
		} else {
			dijit.byId('fileUploaderDlg').set('href', href);
		}
		var self = this;
		dijit.byId('fileUploaderDlg').show();
	}
});