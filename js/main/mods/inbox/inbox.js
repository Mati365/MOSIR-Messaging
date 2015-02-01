define('inbox/inbox', 
        [
            'jquery',
            'utils',
            'form',
            'inbox/report',
            'inbox/flags'
        ], 
        function($, _utils, _form, _report, _flags) {
    /** 
     * Klasa obsługująca skrzynki nadawcze
     * oraz odbiorcze
     */
    var Inbox = _utils.class(
                        new _form.Ajax,
                        function(inbox_id, inbox_type) {
        _form.Ajax.call(this, null, 
                    _utils.enum([
                        'Błąd odświeżania wiadomości!'
                    ], 1));
        this.inbox_id   = inbox_id;
        this.inbox_type = inbox_type;
        /* Rejestrowanie inboxa */
        Inbox.inboxes[inbox_id] = this;
    });
    /** Inicjacja w actions.js */
    Inbox.inboxes = {};

    /** Mruganie paska zakładki */
    Inbox.prototype.tab_title     = '';
    Inbox.prototype.hidden_title  = false;
    Inbox.prototype.blinkTabTitle = function() {
        if(!this.isInbox())
            return;

        this.hidden_title = !this.hidden_title;
        $(document).prop('title', (
            this.hidden_title||!this.tab_title.length?
            '':
            ('##'+this.tab_title+'##'))+
            ' MOSIR');
    };
    /** Inicjacja skrzynki */
    Inbox.prototype.init = function() {
        var self = this;
        setInterval(function() {
            self.refresh();
        }, 3000);
        setInterval(function() {
            self.blinkTabTitle();
        }, 700);
    };
    /**
     * Sprawdzenie czy skrzynka to inbox
     * @return  bool
     */
    Inbox.prototype.isInbox = function() {
        return this.inbox_type === _flags.InboxType.INBOX;
    };
    /**
     * Pobieranie uchwytu wersetów wiadomości
     * @return  Uchwyt body tabeli wiadomości
     */
    Inbox.prototype.getBody   = function() {
        return $('#'+this.inbox_id+' tbody');
    };
    Inbox.prototype.getBodyRows = function() {
        return $('#'+this.inbox_id+' tbody .inbox-message-row');
    };
    /**
     * Callbacki z formularza ajax
     * @param   data    Dane otrzymane
     */
    Inbox.prototype.onSuccess = function(data) {
        if(typeof data['new_records'] === 'undefined' || 
                !data['new_records'].length)
            return;
        $(data['new_records'])
                .prependTo($(this.getBody()))
                .hide()
                .show('slow');
    };
    /**
     * Metody dla poszczególnych wiadomości
     * @param   partial     Partial z ostatnim nullem dla msg_id
     */
    Inbox.prototype.forEach = function(func, param, reload, check_val) {
        var self = this;
        check_val = typeof check_val === 'undefined' || check_val ? true : false;
        $('#'+this.inbox_id)
                .find('.inbox-id-column')
                .each(function() {

            /* Odznaczanie zaznaczonych */
            var check_btn = $(this).prev('td').find('input');
            if($(check_btn).prop('checked') === !check_val)
                return;
            check_btn.prop('checked', !check_val);

            /* param jest stały */
            if(_utils.isSet(self, func)) {
                var params = [ ];
                _utils.copy(param, params);
                params.push(
                            parseInt($(this).text()),
                            $(this).parent());
                self[func].apply(self, params);
            }
        });
        if(reload)
            this.refresh(true);
    };
    /**
     * Usuwanie wiadomości z systemu
     * @param   perm    Pernamentne usuwanie / tylko administrator
     * @param   msg_id  Identyfikator wiadomości
     * @param   row_obj Werset w tabeli do usunięcia
     */
    Inbox.prototype.remove = function(perm, msg_id, row_obj) {
        if(perm)
            this.event('permremove', msg_id);
        else
            this.addFlag(_flags.MessageFlag.REMOVED, msg_id);
        $(row_obj).hide(1000);
    };
    /**
     * Usuwanie flagi do funkcji
     * @param flag      Flaga
     * @param msg_id    Identyfikator wiadomości
     */
    Inbox.prototype.delFlag = function(flag, msg_id) {
        this.event('delflag', 
                    msg_id, 
                    { 
                        flag :  flag,
                        type :  this.inbox_type
                    });
    };
    /**
     * Dodawanie flagi do funkcji
     * @param flag      Flaga
     * @param msg_id    Identyfikator wiadomości
     */
    Inbox.prototype.addFlag = function(flag, msg_id) {
        this.event('addflag', 
                    msg_id, 
                    { 
                        flag :  flag,
                        type :  this.inbox_type
                    });
    };
    /**
     * Nadanie daty realizacji zgłoszenia
     * @param date   Data
     * @param msg_id Identyfikator wiadomości
     */
    Inbox.prototype.setRealizeDate = function(date, msg_id) {
        this.event('realizedate', 
                    msg_id, 
                    { 
                        realize_date :  date 
                    });
    };
    /**
     * Wysyłanie eventów dla poszczególnych wiadomości
     * @param   action  Typ akcji
     * @param   msg_id  Identyfikator wiadomości w bazie
     * @param   opts    Argumenty dodatkowe
     */
    Inbox.prototype.event = function(action, msg_id, opts, module) {
        var data = {
            func:       _utils.getSafeParam(module, 'Inbox'),
            action:     action,
            msg_id:     msg_id
        };
        _utils.copy(opts, data);
        this.send(data);
    };
    /**
     * Odświeżanie skrzynki wiadomości
     * @param   reload  Wczytywanie całej zawartości od nowa
     */
    Inbox.prototype.refresh = function(reload) {
        var self = this;
        if(reload) {
            $(this.getBodyRows()).remove();
            self.refresh();
        } else {
            this.send({
                func        :   'Inbox',
                dom_id      :   this.inbox_id,
                action      :   'refresh',
                last_id     :   _utils.getSafeParam($('#' + this.inbox_id).find('td').eq(1).text(), -1),
                last_dom_id :   _utils.getSafeParam($('#' + this.inbox_id).find('tr').length, -1),
                type        :   this.inbox_type,
                style       :   'inbox-row'
            });
            /* Aktualizacja liczby nie przeczytanych */
            var postfix = this.isInbox()?'nieodczyt.':'nieodeb.',
                unread  = $('#'+this.inbox_id+' tbody tr.inbox-row-blink').length;
            this.tab_title = !unread?'':(unread+' '+postfix);
            $('#'+this.inbox_id+'-badge').text(this.tab_title);
        }
    };
    return {
        Inbox   :   Inbox
    };
});