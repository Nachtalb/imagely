$(document).ready(function () {
    //function to create a new status message
    function addStatus(type, id, message) {
        $('#status').append('<p class=\'alert alert-' + type + '\' id=' + id + 'Status>' + message + '</p>');
    }

    //function to remove each status message by id
    function removeStatus(id) {
        $('#' + id + 'Status').remove();
    }

    //if is empty status message vars
    var statusUsernameEmpty = false; //boolean for UsernameEmpty status message
    var statusPasswordEmpty = false; //boolean for PasswordEmpty status message
    var statusPasswordRepeatEmpty = false; //boolean for PasswordRepeatEmpty status message
    $('#submit').on('click', function () {
        var submit = true; //submit if all is correctly filled-in
        $('form input').each(function () {
            var inputName = $(this).attr('name'); //stores name of inputfield
            var inputValue = $(this).val(); //stores value of inputfield
            if (inputValue === '') {
                addStatus('danger', inputName + 'Empty', 'Bitte füllen Sie folgendes Feld aus: ' + inputName);
                var vars = {};
                vars['status' + inputName + 'Empty'] = true;
                submit = false;
            }
        });
        //submit form if all is filled-in
        if (submit === true) {
            $('#login').submit();
        }
    });
    //validation status message vars
    var statusUsernameValidation = false; //boolean for UsernameValidation status message
    var statusPasswordValidation = false; //boolean for PasswordValidation status message
    var statusPasswordRepeatValidation = false; //boolean for PasswordRepeatValidation status message
    //username validation
    $('form input[name=\'Username\']').on('change', function () {
        var inputName = $(this).attr('name'); //stores name of inputfield
        var inputValue = $(this).val(); //stores value of inputfield
        var regexp = new RegExp('^[a-zA-Z0-9_]+(?:\\.[A-Za-z0-9!#$%&*+/=?^_`{|}~-]+)*@(?!([a-zA-Z0-9]*\\.[a-zA-Z0-9]*\\.[a-zA-Z0-9]*\\.))(?:[A-Za-z0-9](?:[a-zA-Z0-9-]*[A-Za-z0-9])?\\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$'); //email regex
        removeStatus(inputName + 'Empty');
        statusUsernameEmpty = false;
        //test regex on value
        if (!regexp.test(inputValue)) {
            $(this).closest('div.form-group').addClass('has-error');
            if (statusUsernameValidation === false) {
                addStatus('danger', inputName + 'Validation', 'Bitte geben Sie ein korrekter Benutzername (E-Mail) ein');
                statusUsernameValidation = true;
            }
        } else {
            $(this).closest('div.form-group').removeClass('has-error');
            if (statusUsernameValidation === true) {
                removeStatus(inputName + 'Validation');
                statusUsernameValidation = false;
            }
        }
    });
    //password validation
    $('form input[name=\'Password\']').on('change', function () {
        var inputName = $(this).attr('name'); //stores name of inputfield
        var inputValue = $(this).val(); //stores value of inputfield
        var regexp = new RegExp('^(?=.*[A-Z])(?=.*[a-z])(?=.*[()[\\]{}?!$%&/=*+~,.;:<>\\-_]).{9,}$'); //password regex
        removeStatus(inputName + 'Empty');
        statusPasswordEmpty = false;
        //test regex on the value
        if (!regexp.test(inputValue)) {
            $(this).closest('div.form-group').addClass('has-error');
            if (statusPasswordValidation === false) {
                addStatus('danger', inputName + 'Validation', 'Bitte geben Sie ein Passwort ein das ein Grossbuchstabe, Kleinbuchstabe und Sonderzeichen beinhält sowie mindestens 9 Zeichen lang ist');
                statusPasswordValidation = true;
            }
        } else {
            $(this).closest('div.form-group').removeClass('has-error');
            if (statusPasswordValidation === true) {
                removeStatus(inputName + 'Validation');
                statusPasswordValidation = false;
            }
        }
    });
    //reset
    $('#reset').on('click', function () {
        $('#status').empty();
        statusUsernameValidation = false;
        statusPasswordValidation = false;
        statusPasswordRepeatValidation = false;
        statusUsernameEmpty = false;
        statusPasswordEmpty = false;
        statusPasswordRepeatEmpty = false;
        $('form input').each(function () {
            $(this).closest('div.form-group').removeClass('has-error');
        });
    });
});
