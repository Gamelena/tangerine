require(['dojo/_base/declare', "dijit/form/ValidationTextBox", "dijit/form/SimpleTextarea"], function(declare, ValidationTextBox, SimpleTextarea) {
	declare("zwei/form/ValidationTextarea", [ValidationTextBox, SimpleTextarea], {
		invalidMessage : "\u00e9ste campo es requerido",

		firstValidation : true,

		textbox : function() {
			return "";
		},

		postCreate : function() {
			this.inherited(arguments);
			dojo.connect(this.containerNode, 'onkeyup', this, 'validate');
		},

		isValid : function() {
			this.inherited(arguments);
			//the 'true' part of this is to allow validation override
			return arguments[0] == true || this.containerNode.value.length > 0;
		},

		validate : function() {
			this.inherited(arguments);
			var isValid = this.isValid(arguments[0]);

			if (!isValid) {
				this.displayMessage(this.getErrorMessage());
			}
			return isValid;
		},

		onFocus : function() {
			this.validate(this.firstValidation);
			//make sure the first time they click on the box
			//the user don't get an error
			if (this.firstValidation) {
				this.firstValidation = false;
			}
		},

		onBlur : function() {
			//pass in true so that the error bubble goes away
			this.validate(true);
		}
	});
}); 