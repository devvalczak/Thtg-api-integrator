jQuery(document).ready(($) => {
    const formTable = JSON.parse(customFormActionParams.formTable);
    const formNames = JSON.parse(customFormActionParams.formNames);
    const integrations = JSON.parse(customFormActionParams.integrations);
    const apiEndpoint = customFormActionParams.apiEndpoint;

    // generatre new row
    function renderFormRow() {
        const newId = Object.values(formTable).length;
        return '<tr valign="top">' +
            '<td><input type="text" spellcheck="false" name="custom_form_action_form_table[' + newId + '][form_id]" value="" /></td>' +
            '<td><select name="custom_form_action_form_table[' + newId + '][integration]">' +
                integrations.map((integration) => {
                    return `<option value="${integration}">${integration}</option>`;
                }) +
            '</select></td>' +
            '<td><input type="text" name="custom_form_action_form_table[' + newId + '][branch]" value="" /></td>' +
            '<td><input class="monospace" spellcheck="false" type="text" name="custom_form_action_form_table[' + newId + '][callback]" value="" /></td>' +
            '<td><textarea spellcheck="false" class="scriptTextarea monospace" name="custom_form_action_form_table[' + newId + '][script]"></textarea></td>' +
            '<td><input type="checkbox" name="custom_form_action_form_table[' + newId + '][prevent]"/></td>' +
            '<td><a href="#" class="remove-form-row">Usuń</a></td>' +
            '</tr>';
    }

    // add new row
    $('.divi-integration-plugin').on('click', '.add-form-row', (e) => {
        e.preventDefault();
        const formRow = renderFormRow();
        $('#form-table tbody').append(formRow);
        // add empty row with info
        $('#noRows').closest('tr').remove();
    });

    // remove row
    $('#form-table').on('click', '.remove-form-row', function (e) {
        e.preventDefault();
        $(this).closest('tr').remove();

        if ($('#tableBody').children().length === 0) {
            const emptyRow = '<tr valign="top"><td colspan="6" id="noRows">Brak aktywnych integracji</td></tr>';
            $('#tableBody').append(emptyRow);
        }
    });

    // integrations
    Object.values(formTable).forEach((integration) => {
        const formId = integration.form_id;
        const formContainerObject = $('#' + formId);
        let formObject = null;

        if (formContainerObject.is('form')) {
            formObject = formContainerObject
        } else {
            formObject = formContainerObject.find('form');
        }

        // Check is formObject correct
        if (formObject.length) {
            // prevent default actions on target form
            if (integration.prevent) {
                formObject.removeAttr('action')
                formObject.removeAttr('method')
            }

            // inject response handler script
            const script = document.createElement('script')
            script.textContent = integration.script;
            document.body.appendChild(script);
        }

        formObject.on( "submit", (e) => {
            let requestData = {}
            try {
                requestData = {
                    'name': e.target[formNames.name].value,
                    'surname': e.target[formNames.surname].value,
                    'email': e.target[formNames.email].value,
                    'source': e.target[formNames.source].value,
                    'sourceDetails': window.location.protocol + window.location.host,
                    'branch': integration.branch,
                    'callback': integration.callback,
                    'phone': e.target[formNames.phone]?.value ?? '',
                    'message': e.target[formNames.message]?.value ?? '',
                }
            } catch (e) {
                console.error(`Invalid input names on form ${formId}`, e);
                return false;
            }

            if (!requestData.name || !requestData.surname || !requestData.source || !requestData.branch) {
                return false;
            }

            // build JSONP request url
            const apiUrl = `${apiEndpoint}/${integration.integration}?${new URLSearchParams(requestData)}`;

            // create script and append it to body = make JSONP request
            const script = document.createElement('script');
            script.src = apiUrl;
            document.body.appendChild(script);

            e.preventDefault();
            return false;
        });
    })
});