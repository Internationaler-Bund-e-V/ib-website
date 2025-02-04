import $ from 'jquery';
import 'formBuilder';

import '../../Css/backend/app.scss'

/**
*
* this plugin handles the formbuilder backend
*
* @usage: $('#container').myFormBuilderBackend({ajaxSubmitUrl: formSubmitUri});
*/

// '{f:uri.resource(path:\'libsStatic/jquery-ui-sortable/jquery-ui-sortable.min.js\')}',
// '{f:uri.resource(path:\'libs/formBuilder/form-builder.min.js\')}',
// '{f:uri.resource(path:\'libs/formBuilder/form-render.min.js\')}',

(function ($) {

    var defaults = {
        fbEditor: null,
        formBuilder: null,
        settings: {}
    };

    /**
    * ------------------------
    * INIT the plugin
    * ------------------------
    * @returns {jQuery}
    */
    $.fn.myFormBuilderBackend = function (options) {

        var that = this;

        // ------------------------------------
        // define default values and extend with
        // arguments passed to this plugin
        // ------------------------------------
        settings = $.extend({
            formBuilderOptions: {
                subtypes: {
                    paragraph: ['div']
                },
                showActionButtons: false,
                "disableFields": [
                    "autocomplete",
                    "header",
                    "file",
                    "date",
                ],
                disabledAttrs: [
                    'inline',
                    'className',
                    'access',
                    'description',
                    'placeholder',
                    'maxlength',
                    'other',
                    'checked',
                    'style'
                ],
                typeUserAttrs: {
                    'radio-group': {
                        dataLocalValue: {
                            label: 'Abhängigkeiten verwalten',
                            //placeholder: 'input local value',
                            'data-mytype': 'dependencyManager',
                            'autocomplete': "off",
                            'readonly': "readonly"
                        }
                    }
                },
                defaultFields: [{
                    "label": "Absenden",
                    "type": "button",
                    "subtype": "submit",
                    "name": "submit-button-default",
                    "disabledFieldButtons": ['remove', 'other', 'style']
                }],
                typeUserEvents: {

                    button: {
                        onadd: function (fld) {
                            var $nameField = $('.fld-name', fld);

                            if ($nameField.val() == "submit-button-default") {
                                S(fld).find('select[name=subtype]').prop('disabled', true);
                                S(fld).find('input[name=name]').prop('disabled', true);
                                S(fld).find('input[name=value]').prop('disabled', true);
                            }

                        }
                    },
                    paragraph: {
                        onadd: function (fld) {
                            var $nameField = $('.fld-name', fld);
                            // S(fld).find('select[name=subtype]').prop('disabled', true);
                        }
                    },
                    'radio-group': {
                        onadd: function (fld) {
                            //alert('EVENT EVENT EVENT');
                        }
                    }
                },
                fieldRemoveWarn: true,
                i18n: {
                    locale: 'de-DE'
                },
                dataType: 'json'
            },
            ibFormBuilderDataHolder: 'formbuilder-data-holder'
        }, options);


        // ------------------------------------
        // read original formdata (database)
        // from textarea
        // ------------------------------------
        var jsonData = $('textarea#ib-formbuilder-formdataJson').text();

        // extend options with data from database if any
        var fbOtions = settings.formBuilderOptions;
        fbOtions.formData = jsonData;

        fbEditor = document.getElementById(settings.ibFormBuilderDataHolder);
        formBuilder = $(fbEditor).formBuilder(fbOtions);

        // ------------------------------------
        // wait for the formbuilder setup to finish
        // ------------------------------------
        formBuilder.promise.then(function (fb) {
            that.addBackendSubmitClickHandler();
            that.dependenciesHandler();
        });

        return this;
    };

    /**
    * ------------------------------------
    * SUBMIT BUTTON CLICK HANDER
    * ------------------------------------
    * on click the complete form data is read as json
    * and then inserted into the hidden textarea
    * then the form will be submitted
    */
    $.fn.addBackendSubmitClickHandler = function () {
        if ($('#tx_ibformbuilder-getJSON').length) {

            document.getElementById('tx_ibformbuilder-getJSON').addEventListener('click', function () {
                var actualJson = formBuilder.actions.getData('json');
                $('textarea#ib-formbuilder-formdataJson').text(actualJson);

                // after updating the textarea, submit the form
                $('form.tx_ibformbuilder-submit-external').submit();
            });
        }
    };

    $.fn.dependencyRadioClickHandler = function (event) {

    };

    $.fn.openDependencyWindow = function (id) {

        var that = this;
        var currentTarget = $('#' + id);
        var dependencyContainer = $('div#tx_formbuilder_backend_manage_dependencies');

        // ------------------------------------
        // show overlay
        // ------------------------------------
        dependencyContainer.show();

        var parentLabel = $(currentTarget).closest('li.radio-group-field').find('label.field-label').html();
        var parentName = $(currentTarget).closest('div.form-elements').find('input[name=name]').val();

        // ------------------------------------
        // read available form fields
        // ------------------------------------
        var formDataJson = $.parseJSON(formBuilder.formData);
        var actualInputValue = $(currentTarget).val();
        var actualInputValueArray = actualInputValue.split(';');
        //var actualInputName = $(currentTarget).attr('name');
        //var activeDependenciesNames = [];

        // ------------------------------------
        // read available local options for the active radio-group
        // ------------------------------------
        var availableLocalOptions = [];
        $(currentTarget).closest('div.form-elements').find('ol.sortable-options input.option-value').each(function (key, value) {
            availableLocalOptions.push($(value).val());
        });


        // ------------------------------------
        // create an array that holds the
        // local / remote assignment
        // ------------------------------------
        var localRemoteAssgnmentArray = [];
        $(actualInputValueArray).each(function (key, value) {
            var tmp = value.split('#');
            localRemoteAssgnmentArray[tmp[1]] = tmp[0];
        });

        // ------------------------------------
        // read available local options
        // ------------------------------------
        var availableString = "";
        $(availableLocalOptions).each(function (key, value) {
            availableString +=
            '<li class="margin-top">\
					Bei Klick auf den Radio-Button <b>' + value + '</b> in der Gruppe ' + parentLabel + ' wird das folgende Feld eingeblendet: \
				</li>';

            // ------------------------------------
            // januar#termine_januar;februar#termine_februar
            // ------------------------------------
            availableString += '<ul>';

            $(formDataJson).each(function (formDataKey, formDataValue) {
                var mylabel = formDataValue.label + " (" + formDataValue.name + ")";

                console.log('XXXXX', formDataValue.name, parentName);
                var isButton = formDataValue.type === 'button';
                var isParagraph = formDataValue.type === 'paragraph';
                var isSelf = formDataValue.name === parentName;

                if (!isButton && !isParagraph && !isSelf) {
                    // preselect radio for existing selections
                    var attrChecked = "";
                    if (localRemoteAssgnmentArray[formDataValue.name] === value) {
                        attrChecked = 'checked="checked"';
                    }
                    // create list item with configured radio button
                    availableString +=
                    '<li> \
							<input \
								' + attrChecked + ' \
								class="dependency_management_radio" \
								type="radio" \
								id="depend_' + value + '_' + formDataValue.name + '"\
								name="depend_' + value + '" \
								value="' + formDataValue.name + '"\
								data-parent-name="' + value + '"/> \
							<label for="depend_' + value + '_' + formDataValue.name + '">' + mylabel + ' </label> \
						</li>';
                }
            });
            availableString += '</ul>';
        });

        dependencyContainer
        .find('div.dependency_control_section ul')
        .empty()
        .append(availableString);

        // set headline
        dependencyContainer
        .find('h3 span').html(parentLabel);

        // add click handler for radiobuttons inside the overlay
        $('input:radio.dependency_management_radio').change(function () {
            var myResult = [];
            $('.dependency_management_radio:checked').each(function (key, value) {
                myResult.push($(this).data('parent-name') + '#' + $(value).val());
            });

            var combinedResult = myResult.join(';');
            console.log('combinedResult', combinedResult);
            $(currentTarget).val(combinedResult);
        });

        // add click handler to the "remove depenencies" link
        $('a#remove_dependencies_link').click(function (e) {
            e.preventDefault();

            var r = confirm("sollen die bisherigen Abhängigkeiten entfernt werden?");
            if (r === true) {
                $(currentTarget).val("");
                $('div#tx_formbuilder_backend_manage_dependencies').hide();
                alert("die Abhängigkeiten wurden entfernt");
            } else {

            }

        });
    };

    /**
    * ---------------------------------------------------------------
    * DEPENDENCY HANDLER
    * ---------------------------------------------------------------
    * this part handles the dependency management in the backend
    * including the overlay
    */
    $.fn.dependenciesHandler = function () {

        var that = this;
        var dependencyContainer = $('div#tx_formbuilder_backend_manage_dependencies');

        // add a link to open the dependency manager window
        $(('input[data-mytype="dependencyManager"]')).each(function (key, inputField) {

            var xid = $(inputField).attr('id');

            var linkOpen =
            '<a class="open-dependency-manager" \
					data-id="' + xid + '"\
					href="#">\
						Abhängigkeiten verwalten\
				</a>';

            $('<div class="manage-dependency-link-container">' + linkOpen + '</div>').insertBefore(this);
        });


        $('div.manage-dependency-link-container a.open-dependency-manager')
        .click(function (e) {
            e.preventDefault();
            var targetId = $(this).data('id');
            that.openDependencyWindow(targetId)
        });


        // -----------------------------------
        // open dependency management window
        // -----------------------------------
        $('input[data-mytype="dependencyManager"]').click(function (e) {
            e.preventDefault();
            that.openDependencyWindow($(e.currentTarget).attr("id"));
        });

        // ------------------------------------
        // close link click handler
        // ------------------------------------
        dependencyContainer.find('a.close-overlay').click(function (e) {
            e.preventDefault();
            $('div#tx_formbuilder_backend_manage_dependencies').hide();
        });
    };

}($));


$(function () {

    // init backend
    if (jQuery('form.tx_ibformbuilder-submit-external').length) {
        jQuery('form.tx_ibformbuilder-submit-external').myFormBuilderBackend();
    }
});
