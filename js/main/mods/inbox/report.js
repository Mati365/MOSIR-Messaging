/** 
 * Narzędzia wykorzystywane do
 * nadawania wiadomości wychodzących
 */
define('inbox/report', 
        [
            'jquery',
            'utils',
            'form',
            'request',
            'bootbox'
        ], 
        function($, _utils, _form, _request, _bootbox) {
    var Report = _utils.class(
                        new _form.Ajax(null, 
                            _utils.enum([
                                'Nie mogłem wysłać wiadomości!'
                            ], 1),
                            [
                                {id:'#send-msg-btn',action:'report'},
                            ]),
                        function() {});
    /** Odpowiedź serwera */
    Report.prototype.showMessage = function(dom_handle, error, text) {
        $(dom_handle)
                .text(text)
                .removeClass(error ? 'alert-success' : 'alert-danger')
                .addClass(error ? 'alert-danger' : 'alert-success')
                .show('slow')
                .delay(3000)
                .fadeOut();
    }
    Report.prototype.onSuccess = function(data) {
        if(_form.Ajax.prototype.onSuccess.call(this, data)) {
            _bootbox.alert('Wiadomość wysłana sukcesem!');
            $('#mail-comment').html('');
        }
    }
    Report.prototype.onError = function(data) {
        _bootbox.alert(_request.parseError(data));
    }
    /**
     * Odpowiedź, wysłanie wiadomości do systemu
     * @param   content     Treść wiadomości 
     * @param   reply_id    Identyfikator wiadomości, na którą się odpowiada
     * @param   reply_flag  Flaga 
     */
    Report.prototype.response = function(content, reply_id, reply_flag) {
        this.send({
                func        :   'MessageManager',
                action      :   'response',
                content     :   content,
                reply_id    :   reply_id,
                flag        :   _utils.getSafeParam(reply_flag, 0)
        });
    }
    /**
     * Wysyłanie wiadomości do serwera,
     * pobieranie danych z pól
     */
    Report.prototype.report = function() {
        /* Wysyłanie */
        var self = this,
            data = {
                func        :   'MessageManager',
                action      :   'send',
                receiver    :   _utils.getSafeParam($('#mail-receiver').val(), ''),
                content     :   _utils.getSafeParam($('#mail-comment').html(), ''),
                zone        :   _utils.getSafeParam($('#mail-zone').val(), ''),
                object      :   _utils.getSafeParam($('#mail-object').val(), '')
        };
        for(var k in data)
            if(!_utils.isSet(data[k])) {
                self.onError('Formularz posiada puste pola!');
                return false;
            }
        _bootbox.confirm(
                'Czy napewno chcesz wysłać wiadomość?',
                function(result) {
            if(result)
                self.send(data);
        });
    }
    /* Licznik wiadomości */
    $('body').on('change', '#mail-group', function() {
    });
    return new Report;
});