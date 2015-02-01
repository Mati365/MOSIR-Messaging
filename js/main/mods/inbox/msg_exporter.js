/** 
 * Narzędzia wykorzystywane do
 * eksportu wiadomości
 */
define('inbox/msg_exporter', 
        [
            'jquery', 
            'utils',
            'inbox/utils'
        ], 
        function($, _utils) {
    /** 
     * Eksporter wiadomości ze skrzynki
     * odbiorczej/nadawczej, lista eksportowanych
     * typów może być aktualizowana w przyszłości
     */
    var MessageExporter = _utils.class(null, function() {
        var self = this;
        this.export_types = [
            { type : 'csv',  callback : self.toCSV  },
            { type : 'html', callback : self.toHTML }
        ];
    });
    /**
     * Listowanie dostępnych exporterów
     * do menu eksportu
     * @param   obj     Obiekt docelowy listowania
     */
    MessageExporter.prototype.listTypes = function(obj) {
        var self = this,
            html = '';
        (function ret(i) {
            var export_type = self.export_types[i].type;
            html += "<li><a href='javascript:;' class='inbox-"+export_type+"-export'>\
                    <i class='glyphicon glyphicon-export'></i>\
                    Eksport do *."+export_type+"</a></li>";
            $(document).ready(function() {
                $('body').on('click', '.inbox-'+export_type+'-export', function() {
                    self.download(
                            export_type, 
                            require('inbox/utils').parentInbox($(this)).inbox_id, 
                            $(this));
                });
            });
            if(--i>=0)
                ret(i);
        }(this.export_types.length-1));
        $(obj).after(html);
    }
    /**
     * Pobieranie exportera do formatu
     * this.export_types jako tablica 
     * bo iterować się da
     */
    MessageExporter.prototype.getExporter= function(export_type) {
        var callback = null;
        for(var k in this.export_types) {
            var row = this.export_types[k];
            if(row.type === export_type)
                return row.callback;
        }
        return callback;
    }
    /**
     * Pobieranie określonej tabeli
     * @param   export_type     Typ eksportowanego pliku
     * @param   table           Uchwyt do tabeli w dokumencie
     * @param   btn             Przycisk pobierania
     */
    var parse_exceptions = {
        '.inbox-message-content'    :   function(export_type, col, ex_obj) {
            var 
                title   = $(col).find('.inbox-message-title').text().trim(),
                content = $(ex_obj).eq(0).html().trim(),
                html    = export_type === 'html';
            /* Eksport z tytułem */
            if(html && content.length)
                title = '<b>'+title+'</b>';
            if(!content.length)
                content = title;
            else if(content.indexOf(
                        title.indexOf('...')!=-1?title.substr(0, -3):title) === -1)
                content = title+(html?'<br>':' > ')+content;
            /* Eksport z tytułami */
            return $.trim(
                        html?
                            content:
                            $('<div/>', {
                                html : content
                            }).text().replace(/(?:\r\n|\r|\n)/g, " "));
        },
        '.inbox-realization-date'   :   function(export_type, col, ex_obj) {
            return $(ex_obj).attr('data-date');
        }
    };
    /**
     * Dyspozytornia eksportera
     * @param  export_type np. 'html'
     * @param  table       Tabela eksportu
     * @return Eksport w String
     */
    MessageExporter.prototype.export   = function(export_type, table) {
        /* Niektóre kolumny trzeba sparsować */
        var callback            = this.getExporter(export_type);
        if(!_utils.isSet([table, callback]))
            new Error('Exporting error!');

        /* Parsowanie tabeli */
        var rows =  [],
            self =  this;
        $('#'+table+' tr').each(function() {
            var values = [];
            $(this)
                    .find((rows.length?'td':'th')+':not(:first,:last)')
                    .each(function() {
                /* Iteracja przez każdą kolumnę */
                var output = $(this).text();
                for(var k in parse_exceptions) {
                    var ex_obj = $(this).find(k);
                    if(ex_obj.length) {
                        output = parse_exceptions[k](export_type, $(this), ex_obj);
                        break;
                    }
                }
                /* Dodawanie kolumny do listy */
                values.push(output.replace(/\"/g, ""));
            });
            rows.push(values);
        });
        return callback(rows);
    };
    /**
     * Eksport z dialogiem wywoływania pobierania
     * @param  export_type  np. html
     * @param  table        Eksportowana tabela, obiekt
     * @param  btn          Przycisk rodzic wywołujący pobieranie
     */
    MessageExporter.prototype.download = function(export_type, table, btn) {
        /* Pobieranie sparsowanej tabeli */
        btn.attr({
            'href'      :   window.URL.createObjectURL(
                                new Blob(
                                    [this.export(export_type, table)], 
                                    {type: 'text/'+export_type}
                                )),
            'download'  :   'export.'+export_type 
        });
        btn.click();
    };
    /**
     * Eksport do pliku CSV
     * @param   rows    Wersy tabeli
     * @return  String z danymi do pobrania
     */
    MessageExporter.insertBetween = function(rows, left, right) {
        var content = '';
        for(var j in rows)
            content += left+rows[j]+right;
        return content;
    };
    MessageExporter.prototype.toCSV = function(rows) {
        var content = '';
        for(var k in rows)
            content += MessageExporter.insertBetween(rows[k], '"', '",')+'\n';
        return content;
    };
    /*
     * Eksport do pliku HTML
     * @param   rows    Wersy tabeli
     * @return  String z danymi do pobrania
     */
    MessageExporter.prototype.toHTML = function(rows) {
        var content = '<style>\
                            @page {\
                              size:     A4;\
                              margin:   5px;\
                            }\
                            table { \
                                border-collapse:    collapse;\
                                width:              100%;\
                                margin-top:         40px;\
                            }\
                            table, th, td {\
                                border:     1px solid lightgray;\
                                padding:    1px;\
                                font-size:  0.8em;\
                            }\
                            td:first-child,tr:first-child,caption {\
                                font-weight: bold;\
                            }\
                            td:nth-child(3),th:nth-child(2) { \
                                max-width:      350px;\
                                word-wrap:      break-word;\
                                white-space:    pre-wrap;\
                                white-space:    -moz-pre-wrap;\
                                white-space:    -pre-wrap;\
                                white-space:    -o-pre-wrap;\
                                word-wrap:      break-word;\
                            }\
                        </style>\
                        <meta charset="UTF-8">\
                        <table>\
                            <caption style="text-align: center">'+_utils.currentTime()+'</caption>\
                            <tbody>';
        for(var k in rows)
            content += '<tr>'+MessageExporter.insertBetween(rows[k], 
                                '<td>', '</td>')+'</tr>\n';
        return content+'</tbody></table>\
                            <p style="text-align: right">Miejski Ośrodek Sportu i Rekreacji Kołobrzeg</p>';
    };
    return new MessageExporter;
});