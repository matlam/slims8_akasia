/**
 * Change date text to input text
 */
$(document).ready(function() {
    $('.dateChange').click(function(evt) {
        evt.preventDefault();
        var dateText = $(this);
        var loanID = dateText.attr('data');
        // check if it is due or loan date
        var dateToChange = 'due';
        var inputDateClass = 'dateChangeInput dueInput';
        if (dateText.hasClass('loan')) {
            dateToChange = 'loan';
            inputDateClass = 'dateChangeInput loanInput';
        }
        var dateContent = dateText.text().trim();
        var dateInputField = $('<input type="text" value="' + dateContent + '" class="' + inputDateClass + '" maxlength="10" size="10" />');
        dateText.before(dateInputField).hide();
        dateInputField.focus().blur(function() {
                var dateInputField = $(this);
                changeLoanDate(loanID, dateToChange, dateInputField, dateInputField.val());
            } ).keyup(function(evt) {
                    if (evt.keyCode == 13) {
                        changeLoanDate(loanID, dateToChange, dateInputField, dateInputField.val());
                    }
                });
    });
});

/**
 * Function to send AJAX request to change loan and due date
 */
var changeLoanDate = function(intLoanID, strDateToChange, dateElement, strDate)
{
    var dateData = {newLoanDate: strDate, loanSessionID: intLoanID};
    var dateText = $('.'+strDateToChange+'[data="'+intLoanID+'"]');
    if (strDateToChange == 'due') { dateData = {newDueDate: strDate, loanSessionID: intLoanID}; }
    jQuery.ajax({url: 'loan_date_AJAX_change.php', type: 'POST',
        data: dateData,
        dataType: 'json',
        success: function(ajaxRespond) {
                if (!ajaxRespond) {
                    return;
                }
                // evaluate json respons
                var sessionDate = ajaxRespond;
                // update date element
                dateText.html(sessionDate.newDate);
            }
        });
    // remove input date
    dateElement.remove();
    dateText.show();
}