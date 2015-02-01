/* 
 * Rozwijane menu przy logowaniu
 */
define('user_opts', 
        [
            'jquery', 
            'form',
            'utils',
            'login',
            'bootbox'
        ], 
        function($, _form, _utils, _login, _bootbox) {
    /** 
     * Klasa obsługująca ustawienia
     * informacje itp użytkownika
     */
    var UserOpts = _utils.class(
                new _form.Ajax(null, 
                    _utils.enum([
                        'Poprzednie hasło jest nieprawidłowe!'
                    ], 1), []),
                function() {
    });
    /** Odpowiedzi serwera */
    UserOpts.prototype.onSuccess = function(data) {
        if(_form.Ajax.prototype.onSuccess.call(this, data))
            _bootbox.alert('Hasło zmienione! Wciśnij OK by wylogować!', function() {
                $('#logout-btn').click();
            });
    }
    UserOpts.prototype.onError = function(data) {
        _bootbox.alert(data, function() {
            $('#change-password-btn').click();
        });
    }
    /**
     * Zmiana hasła użytkownika
     * po wciśnięciu przycisku w formie
     */
    UserOpts.prototype.changePassword = function() {
        var new_pass = $('#new-password').val();
        if(!new_pass.length)
            _bootbox.alert('Hasło jest puste!');
        else if(new_pass != $('#new-password-confirm').val())
            _bootbox.alert('Hasła nie są zgodne!');
        else
            this.send({
                func        :   'Page',
                action      :   'changepassword',
                old_pass    :   _login.encryptPassword($('#old-password').val()),
                new_pass    :   _login.encryptPassword(new_pass)
            });
    };
    var opts = new UserOpts;
    /**
     * Rejestrowanie dialogu
     * @param  title      Tytuł dialogu
     * @param  button_id  Identyfikator przycisku
     * @param  content_id Identyfikator elementu do skopiowania
     * @param  width      Szerokość dialogu
     * @param  btns       Dodatkowe przyciski
     */
    var regDialog = function(title, button_id, content_id, width, btns) {
        $(button_id).click(function() {
            _bootbox.dialog({
                'title'       : title,
                'message'     : content_id[0]==('#' || '.')?
                                    $(content_id).html():
                                    content_id,
                'buttons'     : btns
            })
                .find('div.modal-content')
                .css({
                    'width'         : (width+'px'),
                    'left'          : '50%',
                    'margin-left'   : (-width/2+'px')
                }); 
        });
    };
    /* Rejestrowanie dialogów */
    regDialog('Informacje o użytkowniku:', 
                '#user-info-btn',
                '#user-info-template', 
                450);
    regDialog('Zmiana hasła:', 
                '#change-password-btn',
                '<div class="form-group">\
                  <label for="firstname">Poprzednie hasło:</label>\
                  <input id="old-password" class="form-control" type="password">\
                </div>\
                <hr>\
                <div class="form-group">\
                  <label for="firstname">Nowe hasło:</label>\
                  <input id="new-password" class="form-control" type="password">\
                </div>\
                <div class="form-group">\
                  <label for="firstname">Powtórz hasło:</label>\
                  <input id="new-password-confirm" class="form-control error" type="password">\
                </div>', 
                350, 
                {
                    success : {
                        label       : '<span class="glyphicon glyphicon-floppy-disk"></span> Zapisz',
                        className   : 'btn-danger',
                        callback    : function() {
                            opts.changePassword();
                        }
                    }
                });
    /* Podkreślanie na czerwono nie takich samych haseł */
    var pass_input = '#new-password, #new-password-confirm';
    $('body').on('input', pass_input, function() {
        var css = $(this).val() == $($(this).is('#new-password')?
                                            '#new-password-confirm':
                                            '#new-password').val()?
                                        [ 'has-error', 'has-success' ]:
                                        [ 'has-success', 'has-error' ];
        $(pass_input)
            .parent()
            .removeClass(css[0])
            .addClass(css[1]);
        $(this)
            .closest('.modal-content')
            .find('button').eq(1)
            .prop('disabled', css[0]!='has-error');
    });
    return opts;
});