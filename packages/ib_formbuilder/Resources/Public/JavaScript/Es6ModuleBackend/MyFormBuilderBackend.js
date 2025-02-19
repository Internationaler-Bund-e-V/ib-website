// MyFormBuilderBackend.js
import $ from "jquery";
import jQuery from "jquery";

// Set jQuery globally
window.jQuery = $;
window.$ = $;

// see // see cms-core/Resources/Public/JavaScript/Contrib/jquery-ui
import "jquery-ui/sortable.js";
import "formbuilder";
import "formrenderer";

// Define the plugin as a function
function MyFormBuilderBackend(options = {}) {

    const defaultSettings = {
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
                            jQuery(fld).find('select[name=subtype]').prop('disabled', true);
                            jQuery(fld).find('input[name=name]').prop('disabled', true);
                            jQuery(fld).find('input[name=value]').prop('disabled', true);
                        }

                    }
                },
                paragraph: {
                    onadd: function (fld) {
                        var $nameField = $('.fld-name', fld);
                        // jQuery(fld).find('select[name=subtype]').prop('disabled', true);
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
    };

    // Merge default settings with user-provided options
    const settings = { ...defaultSettings, ...options }; // or use Object.assign({}, defaultSettings, options);

    // ------------------------------------
    // read original formdata (database)
    // from textarea
    // ------------------------------------
    var jsonData = jQuery('textarea#ib-formbuilder-formdataJson').text();

    // extend options with data from database if any
    var fbOtions = settings.formBuilderOptions;
    fbOtions.formData = jsonData;

    let fbEditorx = document.getElementById(settings.ibFormBuilderDataHolder);
    window.formBuilderx = jQuery(fbEditorx).formBuilder(fbOtions);

    console.log("myFormBuilderBackend: ", settings.testvar);

    // ------------------------------------
    // wait for the formbuilder setup to finish
    // ------------------------------------
    formBuilderx.promise.then(function (fb) {
        addBackendSubmitClickHandler();
        dependenciesHandler();
    });

    // `this` refers to the jQuery element(s) on which this plugin is called
    return this;
    //return this.css({ "background-color": settings.color });
}

function addBackendSubmitClickHandler() {
    if (jQuery('#tx_ibformbuilder-getJSON').length) {

        document.getElementById('tx_ibformbuilder-getJSON').addEventListener('click', function () {
            var actualJson = formBuilderx.actions.getData('json');
            jQuery('textarea#ib-formbuilder-formdataJson').text(actualJson);

            // after updating the textarea, submit the form
            jQuery('form.tx_ibformbuilder-submit-external').submit();
        });
    }
};

function openDependencyWindow(id) {
    var that = this;
    var currentTarget = jQuery('#' + id);
    var dependencyContainer = jQuery('div#tx_formbuilder_backend_manage_dependencies');

    // ------------------------------------
    // show overlay
    // ------------------------------------
    dependencyContainer.show();

    var parentLabel = jQuery(currentTarget).closest('li.radio-group-field').find('label.field-label').html();
    var parentName = jQuery(currentTarget).closest('div.form-elements').find('input[name=name]').val();

    // ------------------------------------
    // read available form fields
    // ------------------------------------
    var formDataJson = jQuery.parseJSON(formBuilderx.formData);
    var actualInputValue = jQuery(currentTarget).val();
    var actualInputValueArray = actualInputValue.split(';');
    //var actualInputName = jQuery(currentTarget).attr('name');
    //var activeDependenciesNames = [];

    // ------------------------------------
    // read available local options for the active radio-group
    // ------------------------------------
    var availableLocalOptions = [];
    jQuery(currentTarget).closest('div.form-elements').find('ol.sortable-options input.option-value').each(function (key, value) {
        availableLocalOptions.push(jQuery(value).val());
    });

    // ------------------------------------
    // create an array that holds the
    // local / remote assignment
    // ------------------------------------
    var localRemoteAssgnmentArray = [];
    jQuery(actualInputValueArray).each(function (key, value) {
        var tmp = value.split('#');
        localRemoteAssgnmentArray[tmp[1]] = tmp[0];
    });

    // ------------------------------------
    // read available local options
    // ------------------------------------
    var availableString = "";
    jQuery(availableLocalOptions).each(function (key, value) {
        availableString +=
            '<li class="margin-top">\
					Bei Klick auf den Radio-Button <b>' + value + '</b> in der Gruppe ' + parentLabel + ' wird das folgende Feld eingeblendet: \
				</li>';

        // ------------------------------------
        // januar#termine_januar;februar#termine_februar
        // ------------------------------------
        availableString += '<ul>';

        jQuery(formDataJson).each(function (formDataKey, formDataValue) {
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
    jQuery('input:radio.dependency_management_radio').change(function () {
        var myResult = [];
        jQuery('.dependency_management_radio:checked').each(function (key, value) {
            myResult.push(jQuery(this).data('parent-name') + '#' + jQuery(value).val());
        });

        var combinedResult = myResult.join(';');
        console.log('combinedResult', combinedResult);
        jQuery(currentTarget).val(combinedResult);
    });

    // add click handler to the "remove depenencies" link
    jQuery('a#remove_dependencies_link').click(function (e) {
        e.preventDefault();
        var r = confirm("sollen die bisherigen Abhängigkeiten entfernt werden?");
        if (r === true) {
            jQuery(currentTarget).val("");
            jQuery('div#tx_formbuilder_backend_manage_dependencies').hide();
            alert("die Abhängigkeiten wurden entfernt");
        } else {

        }
    });
};

function dependenciesHandler() {
    var that = this;
    var dependencyContainer = jQuery('div#tx_formbuilder_backend_manage_dependencies');

    // add a link to open the dependency manager window
    jQuery(('input[data-mytype="dependencyManager"]')).each(function (key, inputField) {
        var xid = jQuery(inputField).attr('id');
        var linkOpen =
            '<a class="open-dependency-manager" \
					data-id="' + xid + '"\
					href="#">\
						Abhängigkeiten verwalten\
				</a>';

        jQuery('<div class="manage-dependency-link-container">' + linkOpen + '</div>').insertBefore(this);
    });

    jQuery('div.manage-dependency-link-container a.open-dependency-manager')
        .click(function (e) {
            e.preventDefault();
            var targetId = jQuery(this).data('id');
            openDependencyWindow(targetId)
        });

    // -----------------------------------
    // open dependency management window
    // -----------------------------------
    jQuery('input[data-mytype="dependencyManager"]').click(function (e) {
        e.preventDefault();
        openDependencyWindow(jQuery(e.currentTarget).attr("id"));
    });

    // ------------------------------------
    // close link click handler
    // ------------------------------------
    dependencyContainer.find('a.close-overlay').click(function (e) {
        e.preventDefault();
        jQuery('div#tx_formbuilder_backend_manage_dependencies').hide();
    });
};

function dependencyRadioClickHandler(event) {
};

// Export the function so it can be registered as a plugin in another file
export default MyFormBuilderBackend;
