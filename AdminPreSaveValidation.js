$(document).ready(function() {
    var canSubmit = false;

    /**
     * Run pre-save validation when the form is submitted and populate the page with the errors returned. If no errors, then re-submit the form normally.
     */
    $('#ProcessPageEdit').on('submit', function(e) {
        if(canSubmit == false) {
            e.preventDefault();

            var formArray = $(this).serializeArray();

            // If the submit_delete button is included in the form submission, bypass pre-save validation and re-submit the form normally
            if(formArray.find(function(element) { return element.name === 'submit_delete' })) {
                canSubmit = true;
                $('#submit_delete').trigger('click');
                return;
            }

            // Get the button that triggered the submit - Fall back to the main submit button if browser doesn't support this.
            var $submitButton = e.originalEvent && e.originalEvent.submitter ? $(e.originalEvent.submitter) : $('#submit_save');

            // Show a spinner on the save button
            var $visibleSaveButtons = $('[name="submit_save"]');
            if(!$visibleSaveButtons.find('i').length) {
                $visibleSaveButtons.children('span').prepend('<i class="fa fa-spin fa-spinner uk-margin-small-right"></i>');
                $visibleSaveButtons.attr('disabled', 1); // Prevent double submits
            }

            // Clear any old errors out
            $('.InputfieldError').remove();
            $('.Inputfield.InputfieldStateError').removeClass('InputfieldStateError');

            $.ajax({
                url: '',
                type: 'post',
                dataType: 'json',
                data: $.param(formArray) + '&preValidate=1'
            }).done(function(data) {
                $visibleSaveButtons.prop('disabled', false); // Re-enable save buttons

                // If no errors, submit page normally
                if(!Object.keys(data).length) {
                    canSubmit = true;
                    $submitButton.trigger('click'); // Trigger a click on whichever submit button originally caused the form submit (triggering submit directly on the form element will not correctly save the page)
                }
                // Otherwise display the new errors
                else {
                    $visibleSaveButtons.find('i').remove(); // Remove spinner

                    for (var fieldName in data) {
                        var errorTxt = data[fieldName];
                        var $errorElement = $('<p class="InputfieldError ui-state-error"><i class="fa fa-fw fa-flash"></i><span>' + errorTxt + '</span></p>');

                        var $inputfield = $('.Inputfield_' + fieldName);
                        $inputfield.addClass('InputfieldStateError');
                        $inputfield.addClass('uk-alert-danger');
                        $inputfield.children('.InputfieldContent').prepend($errorElement);
                    }

                    // Switch to the tab that contains the first error
                    var firstError = $('.InputfieldStateError').eq(0);
                    if(firstError.length) {
                        var tabId = firstError.closest('.WireTab').attr('id');
                        var tab = $('#_' + tabId);
                        tab.trigger('click');

                        // Scroll to the first error
                        var topOfFirstError = firstError.offset().top;
                        $('html,body').animate({scrollTop: topOfFirstError}, 250);
                    }
                }
            }).fail(function(data) {
                $visibleSaveButtons.prop('disabled', false); // Re-enable save buttons
                $visibleSaveButtons.find('i').remove(); // Remove spinner
            });
        }
    });
});