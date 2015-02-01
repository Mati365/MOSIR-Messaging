/**
 * Klasa obsługująca formularz logowania
 */
define('login', 
        [
            'jquery', 
            'utils', 
            'form', 
            'request',
            'sha512'
        ], 
        function($, _utils, _form, _request) {
    /** 
     * Klasa obsługująca formularz logowania
     * odpowiedzialna także za przycisk wyloguj
     * na stronie
     */
    var Login = _utils.class(
                new _form.Ajax(null, 
                    _utils.enum([
                        'Nieprawidłowe hasło!',
                        'Nieprawidłowy login!'
                    ], 1),
                    [
                        {id:'#logout-btn',  action:'logout'}
                    ]),
                function() {
    });
    var login = new Login;
    /** */
    $('#login-form').submit(function() {
        login.login();
        return false;
    });
    /** Odpowiedzi serwera */
    Login.prototype.onSuccess = function(data) {
        if(_form.Ajax.prototype.onSuccess.call(this, data)) {
            $('#login-alert').hide();
            window.location.reload();
        }
    }
    Login.prototype.onError = function(data) {
        $('#login-alert').text(_request.parseError(data)).show();
        $('#login, #password').val('');
    }
    /**
     * By hasło nie zostało przechwycone,
     * kodowane hashowane jest hashem SHA512 
     * @param   pass    Hasło z pola hasła
     */
    Login.prototype.encryptPassword = function(pass) {
        return CryptoJS.SHA512(pass).toString();
    }
    /**
     * Komunikacja z serwerem, logowanie i 
     * wylogowywanie, obsługa w callbackach
     */
    Login.prototype.login = function() {
        this.send({
            func        :   'LoginManager',
            action      :   'login',
            login       :   _utils.getSafeParam($('#login').val(), ''),
            password    :   this.encryptPassword(
                                _utils.getSafeParam(
                                    $('#password').val(), 
                                    ''))
        });
    }
    Login.prototype.logout = function() {
        this.send({
            func        :   'LoginManager',
            action      :   'logout'
        });
    }
    return login;
});