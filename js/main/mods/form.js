/**
 * Moduł/klasa formularza, rodzic 
 * innych obiektów np. Login
 */
define('form', 
        [ 
            'jquery', 
            'utils', 
            'request'
        ], 
        function($, _utils, _request) {
    /**
     * Rejestrowanie akcji przyciśnięcia
     * @param   btns    { id : 'BTN1', action : 'poop' }
     * @param   self    Kontekst wywołujący funkcje 'poop'
     */
    _utils.regAction = function(btns, self) {
        $(document).ready(function() {
            (function ret(btns, index) {
                if(index<0)
                    return;
                
                var btn = btns[index];
                $('body').on('click', btn.id, function() { 
                    self[btn.action]();
                });
                ret(btns, --index);
            }(btns, btns.length-1));
        });
    };
    /** Wrapper na request w postaci klasy */
    var Ajax = _utils.class(null, function(data, err_codes, btns) {
        this.data       =   data        || { };
        this.err_codes  =   err_codes   || { };
        if(_utils.isSet(btns))
            _utils.regAction(btns, this);
    });
    /**
     * Wysyłanie zapytania ajax, metoda 
     * wirtualna przesłaniana w innych klasach
     * @param   data    Dane pola 'data' ajax
     */
    Ajax.prototype.send = function(data) {
        this.data = _utils.getSafeParam(data, this.data);
        _request.send(this.data, this);
    };
    /**
     * Serwer w ajaxie zwraca exit_code,
     * w zależności jaki jest exit_code
     * zwraca taki tekst
     * @param   err_code   Kod błędu
     * @return  Treść błędu
     */
    Ajax.prototype.exitCodeCheck = function(err_code) {
        for(var k in this.err_codes)
            if(this.err_codes[k] == err_code) {
                this.onError(k);
                return true;
            }
        return false;
    };
    /**
     * Metoda wirtualna Wywoływana 
     * podczas poprawnego zwrotu ajax
     * @param   data   Dane 'data' ajax
     * @return  Bool, czy kod to błąd
     */
    Ajax.prototype.onSuccess = function(data) {
        return !this.exitCodeCheck(data['exit_code']);
    };
    return {
        Ajax    :   Ajax
    };
});