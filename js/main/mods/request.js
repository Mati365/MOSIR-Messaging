/** 
 * Narzędzia wykorzystywane do
 * wysyłania zapytań AJAX ze strony
 */
define('request', 
        [
            'jquery',
            'utils'
        ], 
        function($, _utils) {
    /**
     * Wiadomość może przyjść jako error ajaxa/error serwa
     * przemiana błędu php na tekst, jeśli ajax zostawia
     * @param   data    Obiekt błędu
     * @return  Treść błędu
     */
    var parseError = function(data) {
        return typeof data['responseText'] !== 'undefined' ? 
                    data.responseText : 
                    data;
    }
    /**
     * Wysyłanie wiadomości ajax do serwera
     * @param   data        Dane w polu 'data'
     * @param   callback    Metody zwracane jako błąd/sukces
     */
    var send = function(data, callback) {
        $.ajax({
            url         :   'lib/app.php',
            dataType    :   'json',
            type        :   'POST',
            async       :   false, 
            data        :   data,
            success     :   function(data) { 
                callback['onSuccess'](data); 
            },
            error       :   function(data) { 
                var err = parseError(data);
                if(_utils.isSet(callback, 'onError'))
                    callback['onError'](err);
                else
                    console.log(err);
            }
        });
    }
    return {
        parseError  :   parseError,
        send        :   send
    };
});