/* 
 * Narzędzia wykorzystywane do
 * administracji programem
 */
define('admin/sql_editor', 
        [
            'jquery', 
            'utils', 
            'form', 
            'login', 
            'request', 
            'admin/sql_enum',
            'bootbox'
        ], 
        function($, _utils, _form, _login, 
                    _request, _sql_enum, _bootbox) {
    /* 
     * Edycja SQL z poziomu bloczków
     * w kartach, usuwanie/dodawanie/edycja
     */
    var SQLEditor = _utils.class(
                        new _form.Ajax, 
                        function(id, tab_id, data, descriptions) {
        this.id                 =   id;
        this.data               =   data || [];
        this.descriptions       =   descriptions;   //  identyfikator w bazie na nazwę
        this.input_templates    =   {};
        this.tab_id             =   tab_id;
        this.cache              =   {};             //  pamięć podręczna, chowanie otwartych kart

        var self = this;
        $(document).ready(function() {
            self.init();
            SQLEditor.editors.push(self);
        });
    });
    /*
     * Po dodawaniu enuma trzeba
     * przeładować wszyskie tabele SQLa
     */
    SQLEditor.editors = [];
    SQLEditor.reloadTables = function() {
        this.editors.forEach(function(obj) {
            obj.reload();
        });
    };
    /* Odpowiedzi zwrotne serwera */
    SQLEditor.prototype.onSuccess = function(data) {
        if(_utils.isSet(data, 'new_html')) {
            $('#'+this.tab_id).parent().html(data['new_html']);
            this.data           = data['json_data']['rows'];
            this.descriptions   = data['json_data']['template'];
        }
    }
    SQLEditor.prototype.onError = function(data) {
        console.log(_request.parseError(data));
    }
    /**
     * Tworzenie menu edycji rekordu w bazie
     * @param  index    Index elementu w zasobach
     * @return Nowy html
     */
    SQLEditor.prototype.getEditor = function(index) {
        var html = "",
            data = this.data[index],
            lock        =   _utils.isSet(this.cache.tabs) && 
                                Object.keys(this.cache.tabs).length>1,
            input_opt   =   lock?'disabled':'';
        for(var k in data) {
            if(!_utils.isSet(this.descriptions, k))
                continue;
            html += "<div class='form-group'>\
                        <label for='"+k+"'>"+this.descriptions[k][0]+"</label>"
                    +(!_utils.isSet(data[k], 'data')?
                        "<input type='text' name='"+k+"' class='editor-input form-control' \
                            class='"+this.id+'-'+k+"' placeholder='"+data[k]+"' "+this.descriptions[k][1]+' '+input_opt+'>':
                         _sql_enum.getEnum(k, data[k], this.id, input_opt))
                    +'</div>';
        }
        return html+'<div class="btn-toolbar">'+
                    (lock?'':'<button type="button" class="btn btn-danger '+this.id+'-update">Aktualizuj</button>') +
                    '<button type="button" class="btn btn-default '+this.id+'-reset-editor">Cofnij</button></div>';
    }
    /**
     * Po kliknięciu 'Aktualizuj' wszystkie pola
     * wczytywane są do tablicy asocjacyjnej
     * @param   obj     Przycisk
     * @return  Tablica ze wszystkimi polami 
     */
    SQLEditor.prototype.getUpdatedRow = function(obj) {
        var rows = {};
        $(obj).parents('.thumbnail')
                .find('.editor-input')
                .each(function() {
            /* Przerabianie ustawień na jsona */
            var name = $(this).attr('name');
            if(
                    ($(this).is(':text')        &&  $(this).val().length)   || 
                    ($(this).is('select')       &&  $(this).val() != -1)    ||
                    ($(this).is(':checkbox:checked'))) {
                var val = name=='password'?
                                    _login.encryptPassword($(this).val()):
                                    $(this).val();
                if(_utils.isSet(rows, name)) {
                    rows[name] = _utils.getSafeArray(rows[name]);
                    rows[name].push(val);
                } else
                    rows[name] = val;
            }
        });
        return rows;
    }
    /**
     * Przeładowywanie całej tabeli wiadomości
     */
    SQLEditor.prototype.reload = function() {
        /* Wysyłanie pustego zapytania */
        this.send({
            func        :   'SettingsPage',
            table       :   this.id,
            action      :   'refresh'
        }, false);
    }
    /**
     * Wysyłanie zapytania i wczytywanie całych
     * tabel od początku
     * @param  data     Dane z formularza edycji       
     */
    SQLEditor.prototype.updateTables = function(data) {
        this.send(data);
        SQLEditor.reloadTables();
    }
    /**
     * Pobieranie uchwytu edytora rekordu
     * @param   obj     Obiekt wewnątrz tego edytora
     */
    SQLEditor.prototype.getRow = function(obj) {
        return $(obj).closest('div.thumbnail');
    }
    /**
     * Pobieranie indexksu uchwytu edytora rekordu
     * @param   obj     Obiekt wewnątrz tego edytora
     */
    SQLEditor.prototype.getRowIndex = function(obj) {
        return this.getRow(obj).index()-1; // indexuje od 1
    }
    /** 
     * Przypisywanie eventów do przycisków
     * oraz inicjalizacja komponentu
     */
    SQLEditor.prototype.init = function() {
        var self = this;
        /**
         * Pobieranie tabeli enumeratora
         * @param   obj     Kontrolka
         */
        var getEnumTable = function(obj) {
            return $(obj).parents('.form-group')
                            .children()
                            .last().val();
        }
        /**
         * Scrollowanie do obiektu
         * @param  obj  Obiekt, do którego się scrolluje
         */
        var scrollTo = function(obj) {
            setTimeout(function() {
                $('html, body').animate({
                    scrollTop: $(obj).last().offset().top
                }, 100);
            }, 100);
        };
        /**
         * Zatwierdzanie ustawień i 
         * przeładowanie listy, lista cały
         * czas jest widoczna
         * @param   obj     Kontrolka
         * @param   data    dane ajax
         */
        var refreshChanges = function(obj, data) {
            var parent  =   $(obj).parents('.form-group');
            self.updateTables(data);

            var root =  $('#'+self.tab_id).children();
            for(var index in self.cache.tabs)
                $(root)
                    .eq(parseInt(index)+1)  //  +1 bo przycisk add
                    .find('button').eq(0)
                    .click();
        };
        /**
         * Przycisk obsługi dodanie nowego
         * enumeratora do listy enumeratorów
         */
        $('body').on('click', 
                        '.'+this.id+'-enum-add',
                        function() {
            var input = $(this).parent().prev(),
                value = $(input).val();
            if(value.length) {
                refreshChanges($(this), {
                    func        :   'SettingsPage',
                    action      :   'insert',
                    table       :   getEnumTable($(this)),
                    fetch_table :   false,
                    name        :   value
                });
                scrollTo('.'+self.id+'-enum-add');
            }
        });
        /**
         * Przycisk obsługi usunięcia
         * enumeratora z listy enumeratorów
         */
        $('body').on('click', 
                        '.'+this.id+'-enum-remove',
                        function() {
            var dom_obj  = $(this),
                obj     =  $(this).parent().next(),
                input = obj.find('input:eq(0)'),
                index = input.val();
            _bootbox.confirm(
                    'Napewno chcesz skasować enum "'+$(obj).text().trim()+'"? Jeśli jest powiązany z elementem nie zostanie usunięty.', 
                    function(result) {
                if(!result)
                    return;

                input.attr('checked', false);
                refreshChanges($(dom_obj), {
                    func        :   'SettingsPage',
                    action      :   'delete',
                    table       :   getEnumTable($(dom_obj)),
                    fetch_table :   false,
                    id          :   index
                });
                scrollTo('.'+self.id+'-enum-remove');
            }); 
        });
        /**
         * Przycisk edycji i resetu pola edycji
         * rekordu, jeśli kliknie się na edit 
         * pole wyjeżdża, jeśli reset chowa
         */
        $('body').on('click', 
                        '.'+this.id+'-edit, .'+this.id+'-reset-editor', 
                        function() {
            self.cache.tabs = _utils.getSafeParam(
                                self.cache.tabs, 
                                { });
            var root_obj    =   self.getRow($(this)),
                index       =   self.getRowIndex($(this)),
                tabs        =   self.cache.tabs;
            /* Chowanie informacji o polu do cache */
            if($(this).hasClass(self.id+'-edit')) {
                tabs[index] = $(root_obj).html();
                $(root_obj).html(
                        self.getEditor(index));
                $(root_obj)
                        .hide()
                        .show('fast');
            } else {
                $(root_obj).html(tabs[index]);
                delete tabs[index];
            }
        });
        /**
         * Pobieranie ID SQL elementu z pola
         * edycji rekordu
         * @param  obj      Obiekt, przycisk w polu edycji rekordu
         * @return Identyfikator w bazie SQL rekordu
         */
        var getSqlID = function(obj) {
            return $(obj)
                        .parents('.thumbnail')
                        .find('input')
                        .eq(0).attr('placeholder');
        };
        /**
         * Aktualizowanie rekordu
         */
        $('body').on('click', 
                        '.'+this.id+'-update',
                        function() {
            self.updateTables({
                func        :   'SettingsPage',
                action      :   'update',
                table       :   self.id,
                id          :   getSqlID($(this)),
                data        :   self.getUpdatedRow($(this))
            });
            delete self.cache.tabs[self.getRowIndex($(this))];
        });
        /**
         * Kasowanie rekordu
         */
        $('body').on('click', 
                        '.'+this.id+'-delete',
                        function() {
            var dom_obj = $(this).parents('.thumbnail');
            _bootbox.confirm(
                    'Napewno chcesz usunąć ten obiekt? Jeśli jest powiązany z elementem nie zostanie usunięty.', 
                    function(result) {
                if(!result)
                    return;
                /* Kasowanie */
                $(dom_obj).hide('slow', function() {
                    self.updateTables({
                        func        :   'SettingsPage',
                        action      :   'delete',
                        table       :   self.id,
                        id          :   $(dom_obj)
                                            .find('input')
                                            .eq(0)
                                            .attr('placeholder')
                    });
                });
            });
        });
        /**
         * Dodanie rekordu
         */
        $('body').on('click', 
                        '.'+this.id+'-add',
                        function() {
            self.cache.tabs = {};
            self.updateTables({
                func        :   'SettingsPage',
                action      :   'insert',
                table       :   self.id
            });
        });
        /**
         * W razie edycji placeholder
         * przepisywany do textboxa
         */
        $('body').on('click', 
                        '.editor-input', 
                        function() {
            if(!$(this).val().length && 
                    $(this).attr('placeholder').length) {
                $(this).val($(this).attr('placeholder'));
                $(this).css({
                    'font-weight'   :   'bold'
                });
            }
        });
    }
    return {
        SQLEditor   :   SQLEditor
    };
});