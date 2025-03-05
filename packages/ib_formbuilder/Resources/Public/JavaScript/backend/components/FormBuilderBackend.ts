import mergeDeep from '../libs/DeepMerge';
import 'formBuilder';
import $ from 'jquery';

class FormBuilderBackend {
    protected fbEditor:HTMLDivElement;
    protected formBuilder:any;
    protected settings:any =         {
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
                    onadd: function (fld:Element|HTMLElement|JQuery<HTMLElement>) {
                        var $nameField = $('.fld-name', fld);

                        if ($nameField.val() == "submit-button-default") {
                            $(fld).find('select[name=subtype]').prop('disabled', true);
                            $(fld).find('input[name=name]').prop('disabled', true);
                            $(fld).find('input[name=value]').prop('disabled', true);
                        }

                    }
                },
                paragraph: {
                    onadd: function (fld: any) {
                        var $nameField = $('.fld-name', fld);
                        // $(fld).find('select[name=subtype]').prop('disabled', true);
                    }
                },
                'radio-group': {
                    onadd: function (fld: any) {
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

    protected formElement:HTMLFormElement|null;
    protected dependencyContainer:HTMLElement|null = null;

    constructor(element:HTMLFormElement, options = {}) {
        this.formElement = element;
        mergeDeep(this.settings, options);


        // ------------------------------------
        // read original formdata (database)
        // from textarea
        // ------------------------------------
        const jsonData = (document.getElementById('ib-formbuilder-formdataJson') as HTMLTextAreaElement).value;

        // extend options with data from database if any
        let fbOtions = this.settings.formBuilderOptions;
        fbOtions.formData = jsonData;

        this.fbEditor = document.getElementById(this.settings.ibFormBuilderDataHolder) as HTMLDivElement;
        this.formBuilder = ($(this.fbEditor) as any).formBuilder(fbOtions);

        // ------------------------------------
        // wait for the formbuilder setup to finish
        // ------------------------------------
        this.formBuilder.promise.then(() => {
            this.addBackendSubmitClickHandler();
            this.dependenciesHandler();
        });

        return this;
    }

    addBackendSubmitClickHandler() {
        let button:HTMLButtonElement = document.getElementById('tx_ibformbuilder-getJSON') as HTMLButtonElement;
        if (!button) {
            return;
        }

        button.addEventListener('click', () => {
            var actualJson = this.formBuilder.actions.getData('json');
            button.value = actualJson;

            // after updating the textarea, submit the form
            (document.getElementById('form.tx_ibformbuilder-submit-external') as HTMLFormElement).submit();
        });
    }

    dependencyRadioClickHandler() {

    }

    openDependencyWindow(id:string) {

        const currentTarget:HTMLInputElement = document.getElementById(id)! as HTMLInputElement;
        this.dependencyContainer = document.getElementById('tx_formbuilder_backend_manage_dependencies')! as HTMLElement;

        // ------------------------------------
        // show overlay
        // ------------------------------------
        this.dependencyContainer!.style.display = 'block';

        var parentLabel = $(currentTarget).closest('li.radio-group-field').find('label.field-label').html();
        var parentName = $(currentTarget).closest('div.form-elements').find('input[name=name]').val();

        // ------------------------------------
        // read available form fields
        // ------------------------------------
        var formDataJson = JSON.parse((window as any).formBuilder.formData);
        var actualInputValue = currentTarget.value;
        var actualInputValueArray = actualInputValue.split(';');
        //var actualInputName = $(currentTarget).attr('name');
        //var activeDependenciesNames = [];

        // ------------------------------------
        // read available local options for the active radio-group
        // ------------------------------------
        var availableLocalOptions:Array<string> = [];
        $(currentTarget).closest('div.form-elements').find('ol.sortable-options input.option-value').each(function (key, value: Element|HTMLElement|JQuery<HTMLElement>) {
            availableLocalOptions.push($(value).val() as string);
        });


        // ------------------------------------
        // create an array that holds the
        // local / remote assignment
        // ------------------------------------
        var localRemoteAssgnmentArray:any = {};
        $(actualInputValueArray).each(function (key, value) {
            var tmp = ((value as unknown) as string).split('#');
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
                //var mylabel = formDataValue.label + " (" + formDataValue.name + ")";
                const formDataName: string = (formDataValue as any).name;
                const formDataLabel: string = (formDataValue as any).label + ' (' + formDataName + ')';
                const formDataType: string = (formDataValue as any).type;
                console.log('XXXXX', formDataName, parentName);
                var isButton = formDataType === 'button';
                var isParagraph = formDataType === 'paragraph';
                var isSelf = formDataName === parentName;

                if (!isButton && !isParagraph && !isSelf) {
                    // preselect radio for existing selections
                    var attrChecked = "";
                    if (localRemoteAssgnmentArray[formDataName] === value) {
                        attrChecked = 'checked="checked"';
                    }
                    // create list item with configured radio button
                    availableString +=
                    '<li> \
							<input \
								' + attrChecked + ' \
								class="dependency_management_radio" \
								type="radio" \
								id="depend_' + value + '_' + formDataName + '"\
								name="depend_' + value + '" \
								value="' + formDataName + '"\
								data-parent-name="' + value + '"/> \
							<label for="depend_' + value + '_' + formDataName + '">' + formDataLabel + ' </label> \
						</li>';
                }
            });
            availableString += '</ul>';
        });

        $(this.dependencyContainer).find('div.dependency_control_section ul').empty().append(availableString);

        // set headline
        $(this.dependencyContainer).find('h3 span').html(parentLabel);

        // add click handler for radiobuttons inside the overlay
        $('input:radio.dependency_management_radio').change(function () {
            var myResult:Array<string> = [];
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
    }

    /**
    * ---------------------------------------------------------------
    * DEPENDENCY HANDLER
    * ---------------------------------------------------------------
    * this part handles the dependency management in the backend
    * including the overlay
    */
    dependenciesHandler() {

        var dependencyContainer = $('div#tx_formbuilder_backend_manage_dependencies');

        // add a link to open the dependency manager window
        document.querySelectorAll('input[data-mytype="dependencyManager"]').forEach((inputField) => {
            var xid = (inputField as HTMLElement).id;
            var linkOpen =
            '<a class="open-dependency-manager" \
					data-id="' + xid + '"\
					href="#">\
						Abhängigkeiten verwalten\
				</a>';

            $('<div class="manage-dependency-link-container">' + linkOpen + '</div>').insertBefore(inputField);
        });

        // -----------------------------------
        // open dependency management window
        // -----------------------------------
        document.querySelectorAll('div.manage-dependency-link-container a.open-dependency-manager, input[data-mytype="dependencyManager"]').forEach((element) => {
            element.addEventListener('click', (e) => {
                e.preventDefault();
                this.openDependencyWindow((e.currentTarget as HTMLElement).id);
            });
        });

        // ------------------------------------
        // close link click handler
        // ------------------------------------
        dependencyContainer.find('a.close-overlay').on('click', e => {
            e.preventDefault();
            document.getElementById('tx_formbuilder_backend_manage_dependencies')!.style.display = 'none';
        });
    };

}

export default FormBuilderBackend;
