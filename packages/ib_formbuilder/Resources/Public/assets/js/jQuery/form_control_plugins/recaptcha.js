/**
 * Add Recaptcha class
 */

// configure the class for runtime loading
if (!window.fbControls) window.fbControls = [];
window.fbControls.push(function (controlClass) {
	/**
	 * Star rating class
	 */
	class controlRecaptcha extends controlClass {

		/**
		 * Class configuration - return the icons & label related to this control
		 * @returndefinition object
		 */
		static get definition() {
			return {
				icon: 'x',
				i18n: {
					default: 'Google Recaptcha'
				}
			};
		}

		/**
		 * javascript & css to load
		 */
		configure() {
			this.xy = 'ab';
		}

		/**
		 * build a text DOM element, supporting other jquery text form-control's
		 * @return {Object} DOM Element to be injected into the form.
		 */
		build() {

			var input_2 = this.markup('input', null, {
				name: "name_field_2",
				id: this.id,
				type: 'text'
			});

			return this.markup('div', [input_1, label_1, input_2, label_2], {className: 'wrapper'});
		}


		/**
		 * onRender callback
		 */
		onRender() {
			console.log('ON RENDER');
			$('#' + this.config.name).html('dependency manager');
		}
	}

	// register this control for the following types & text subtypes
	controlClass.register('addDependency', controlAddDependency);
	return controlAddDependency;
});