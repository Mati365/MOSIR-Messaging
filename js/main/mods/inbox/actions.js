/**
 * Front-end skrzynki pocztowej
 */
define('inbox/actions', 
        [
            'jquery',
            'dom_ready',
            'utils',
            'inbox/inbox',
            'inbox/utils',
            'inbox/flags',
            'inbox/report',
            'request',
            'page',
            'bootbox',
            /* */
            'inbox/msg_exporter',
            'datepicker'
        ], 
        function($, _dom_ready, _utils, _, _i_utils, 
                _flags, _report, _request, _page, _bootbox) {
    /**
     * Rejestrowanie kontrolek obsługujących
     * dane flagi
     * @param   selector    Selektory
     * @param   callback    Callback kliknięcia
     */
    var regClick = function(selector, callback) {
        $('body').on('click', selector, callback);
    };
    /**
     * Dodawanie flagi bitowej o wiadomości
     * @param   comp_class     Klasy komponentów
     * @param   flag           Obsługiwana flaga, jesli array to drugie dla outbox
     * @param   add            Dodawanie/usuwanie flagi
     * @param   callback       Callback po kliknięciu
     */
    var regFlag = function(comp_class, flag, add, callback) {
        var func = add?'addFlag':'delFlag';
        regClick('.'+comp_class[0]+',.'+comp_class[1],
                    function() {
            var parent_inbox = _i_utils.parentInbox($(this));
            if(Array.isArray(flag))
                flag = parent_inbox.isInbox()?flag[0]:flag[1];

            if(comp_class.length === 1 ||
                    $(this).attr('class') === comp_class[1])
                parent_inbox.forEach(func, [ flag ], true);
            else
                parent_inbox[func](
                            flag, 
                            _i_utils.messageIndex($(this)));
            if(_utils.isSet(callback))
                callback($(this));
        });
    };
    /**
     * Zaznaczenie wszystkich checkboxów
     * @param   $obj        Kontrolka wysyłająca
     * @param   $checked    Zaznaczyć
     */
    var selectAll = function(obj, checked) {
        _i_utils
                .parentInbox(obj)
                .forEach(null, null, false, checked);
    };
    /**
     * Wyświetlanie dialogu potwierdzającego
     * @param   obj     Kontrolka wysyłająca
     * @param   text    Text w prompt
     * @param   reply   Wiadomość generowana/zwrotna
     */
    var showCallbackConfirm = function(obj, text, reply) {
        _bootbox.confirm(text, function(result) {
            if(result)
                _report.response(
                        reply+' <br><i>Wiadomość wygenerowana automatycznie</i>', 
                        _i_utils.messageIndex($(obj)),
                        _flags.MessageFlag.GENERATED);
        }); 
    };
    /**
     * Pobieranie listy stref na podstawie 
     * zaznaczonego obiektu
     * @param  object_select    Selektor/obiekt listy zaznaczania obiektów
     * @param  zone_select      Selektor/obiekt listy zaznaczania stref
     */
    var fetchZonesList = function(object_select, zone_select) {
        // Wysyłanie i odbieranie nowych eventów
        $(zone_select).html('Ładuje..');
        _request.send({
            func        :   'Page',
            action      :   'fetchzones',
            object      :   $(object_select).val(),
        }, {
            onSuccess  :   function(data) {
                $(zone_select).html(data['new_html']);
            }
        });
    };
    /**
     * Wraz z dodaniem wiadomości trzeba aktualizować
     * akcje dla przycisków(pola combi wiadomości mają ID)
     */
    _.Inbox.refreshActions = function() {
        /**
         * Obsługa filtrowania wiadomości
         */
        $('body').on('change', '#mail-object', function() {
            fetchZonesList($(this), $('#mail-zone'));
        });
        $('body').on('change', '.object_filter', function() {
            fetchZonesList($(this), $(this)
                                .parents('tr')
                                .next()
                                .find('.zone_filter'));
            $('.zone_filter')
                .val($('.zone_filter').val())
                .trigger('change');
        });
        /**
         * Belka tytułowa wiadomości wywołująca
         * jej rozwinięcie
         */
        var show_messages   =   0,
            col_width       =   0; // ilość wyjechanych memu
        regClick('.inbox-message-title', function() {
            var content =   $(this).next(),
                row     =   $(content).closest('tr'),
                inbox   =   _i_utils.parentInbox($(this)),
                id      =  '#'+inbox.inbox_id,
                cols_id =   id+' thead tr th:nth-child(5), '+id+' thead tr th:nth-child(6)';
            /* Puste się nie rozwija */
            if(!$(content).text().trim().length)
                _bootbox.alert('Wiadomość za krótka by rozwinąć!')
            else if(!$(content).hasClass('in')) {
                if(!$(content).find('.mail-comment').length && inbox.isInbox())
                    var last_label = $(content).find('label:last');
                    if($(last_label).text() === 'Odpowiedź:')
                        $(last_label)
                            .after(_page.getReplyForm());

                /* Werset powinien być biały */
                $(row).css('background-color', 'white');
                /* Animacja kurczenia kolumn */
                if(!col_width)
                    col_width = $(cols_id).eq(0).outerWidth();
                $(cols_id).animate({width: '20px'}, 500);
                /* Rejestracja rozwiniętego */
                inbox.show_messages = _utils.getSafeParam(inbox.show_messages, 0)+1;
            } else {
                $(row).css('background-color', '');
                $(content)
                    .find('.mail-comment')
                    .nextUntil('.pull-left').remove()
                    .andSelf().remove();
                if(!--inbox.show_messages)
                    $(cols_id).animate({width: col_width+'px'}, 500);
            }
        });
        /**
         * Przycisk nadania daty relizacji
         */
        $('body').on('focus', '.inbox-realization-date', function() {
            $(this).datepicker()
                    .on('changeDate', function() {
                /* bug w datapicker */
                var new_date = $(this).eq(0).children().first().val();
                _i_utils
                    .parentInbox($(this))
                    .setRealizeDate(
                        new_date,
                        _i_utils.messageIndex($(this)));
                $(this).datepicker('hide');
                showCallbackConfirm(
                    $(this),
                    'Powiadomić o zmianie terminu realizacji?',
                    'Nadanie daty realizacji zgłoszenia: <b>'+new_date+'</b>');

                /* odświeżanie */
                _i_utils
                    .parentInbox($(this))
                    .refresh(true);
            });
        });
        /**
         * Podpinanie przycisków eventów/filtrów 
         * pod ustawienia po prawej stronie
         */
        $(function ret(index) {
            if(index < 0)
                return;
            var setting = _i_utils.settings[index],
                name    = setting.name,
                boolToFilterFlag    =   function(checked) {
                    return checked?setting.def_value:0;
                };

            $('.'+name).unbind();
            $('.'+name).change(function() {
                var inbox = _i_utils.parentInbox(
                            $(this).closest('div').prev()
                            .find('table'));
                inbox.send({
                    func        :   'SettingsManager',
                    action      :   'setting',
                    name        :   name,
                    value       :   $(this).is(':checkbox')?
                                                boolToFilterFlag($(this).is(':checked')):
                                                $(this).val()
                });
                inbox.refresh(true);
            });
            ret(--index);
        }(_i_utils.settings.length-1));
        /**
         * Przycisk otwarcia menu filtru
         */
        regClick('.inbox-show-filter', function() {
            var message_table   =   $(this).closest('table').parents(':eq(0)'),
                filter_table    =   message_table.next(),
                icon            =   "<span class='glyphicon glyphicon-filter'></span>";
            /* Pokazywanie */
            if(filter_table.is(':visible')) {
                $(this).html(icon+' Pokaż');
                filter_table
                    .hide('fast', function() {
                        message_table
                            .removeClass('col-xs-9')
                            .addClass('col-xs-12');
                    });
            } else {
                $(this).html(icon+' Ukryj');
                message_table
                    .removeClass('col-xs-12')
                    .addClass('col-xs-9');
                filter_table
                    .show('fast');
            }
        });
        /* Nadawanie eventow */
        regClick('.inbox-select-all,.inbox-deselect-all', function() {
            selectAll(
                    $(this),
                    $(this).attr('class') !== 'inbox-select-all');
        });
        regClick('.inbox-remove', function() {
            _i_utils
                .remove($(this), false);
        });
        regClick('.inbox-perm-remove', function() {
            _i_utils
                .remove($(this), true);
        });
        regClick('.inbox-content-refresh', function() {
            _i_utils
                .parentInbox($(this))
                .refresh(true);
        });
        regClick('.inbox-content-print', function() {
            var _exporter = require('inbox/msg_exporter');
            $('#printable')
                .html(
                    _exporter.export(
                        'html',
                        _i_utils.parentInbox($(this)).inbox_id))
                .show();
            $('#non-printable').hide();
            {
                window.print();
            }
            $('#printable').empty().hide();
            $('#non-printable').show();
        });
        regClick('.inbox-reply-button', function() {
            var collapse_content    =   $(this)
                                            .closest('.panel-collapse')
                                            .find('.mail-comment');
            _report.response(
                        collapse_content.html(), 
                        _i_utils.messageIndex($(this)));
            collapse_content.val('');
        });
        /* WIADOMOŚĆ DLA KAŻDEJ ZREALIZOWANEJ */
        regFlag(['inbox-done-button','inbox-mark-done'], 
                        _flags.MessageFlag.DONE, 
                        true,
                        function(obj) {
            if($(obj).is('#inbox-done-button'))
                showCallbackConfirm(
                    $(obj),
                    'Powiadomić o zakończeniu realizacji?',
                    'Zgłoszenie zostało oznaczone jako <b>zrealizowane</b>.');
        });
        regFlag(['inbox-view-button','inbox-mark-viewed'], 
                        _flags.MessageFlag.VIEWED,
                        true,
                        function(obj) {
            if(_i_utils.parentInbox($(obj)).isInbox())
                $(obj)
                    .parent()
                    .removeClass('inbox-row-blink');
        });
        regFlag(['inbox-mark-starred'], 
                        _flags.MessageFlag.STARRED,
                        true);
        regFlag(['inbox-unmark-starred'], 
                        _flags.MessageFlag.STARRED,
                        false);
    };
    _dom_ready(function() {
        _.Inbox.refreshActions();
        /* Inicjacja skrzynek po załadowaniu strony */
        Object.keys(_.Inbox.inboxes).forEach(function(key, index) {
            this[key].init();
        }, _.Inbox.inboxes);
        /* Ściąganie list stref */
        $('#mail-object').trigger('change');
    });
});