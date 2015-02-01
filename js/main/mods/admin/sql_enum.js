/* 
 * Informacje/rendering enuma
 * dla SQLeditor
 */
define('admin/sql_enum', 
        [
            'jquery', 
            'utils',
            'admin/flags'
        ], 
        function($, _utils, _) {
    /*
     * Dane o enumeratorze zwrócone przez
     * serwer, nie sparsowane
     */
    var EnumData = _utils.class(null, 
            function(name, table, data, def, entry_col) {
        this.name       =   name;
        this.table      =   table;
        this.data       =   data;
        this.def        =   def;
        this.entry_col  =   entry_col;
    });
    /**
     * Informacje potrzebne do wygenerowanie enumeratora
     * @param  enum_data    Pola enumeratora
     * @param  id           Identyfikator dom
     * @param  input_opt    Opcjonalne argumenty np. disabled
     */
    var GenData = _utils.class(null, function(enum_data, id, input_opt) {
        this.data       =   enum_data;
        this.id         =   id;
        this.input_opt  =   input_opt;
    });
    /*
     * TODO: Refactoring
     * Renderowanie enumu pojedynczego do dokumentu,
     * pojedyncze polo combo
     * @param   gen_data   Dane potrzebne do generowania
     * @return  Nowo wygenerowany html
     */
    var getSingleEnum = function(gen_data) {
        var html        =   "<select class='editor-input form-control' name='"+
                                gen_data.data.name+
                            "'>",
            def_found   =   false;

        gen_data.data.data.forEach(function(element) {
            var val     = element[gen_data.data.entry_col];
            def_found   = def_found || val==gen_data.data.def;
            html += "<option value='"+val+"' "+
                        (val==gen_data.data.def?"selected":"")
                        +">"+element.name+"</option>";
        });
        if(!def_found && _utils.isSet(gen_data.data.def))
            html += "<option value='-1' selected "+gen_data.input_opt+">"+
                        gen_data.data.def+
                    "</option>";
        return html+"</select>";
    };
    /**
     * Generowanie enumeratora wielowyborowego, 
     * w postaci listy checkbox
     * @param   gen_data   Dane potrzebne do generowania
     * @return  Nowo wygenerowany html
     */
    var getMultipleEnum = function(gen_data) {
        var html =  '';

        gen_data.data.def = _utils.getSafeArray(gen_data.data.def);
        gen_data.data.data.forEach(function(element) {
            html += "<div class='row'><div class='col-md-1'>"+
                        (element.name!='*'?"<span class='glyphicon glyphicon-remove "+
                            gen_data.id+"-enum-remove' style='cursor: pointer;color: red'></span>":"")+
                        "</div><div class='col-md-10' style='padding: 0px;height:24px;'><label title='"+element.name+"'>\
                            <input class='editor-input' name='"+
                                gen_data.data.name+"' type='checkbox' value='"+element[gen_data.data.entry_col]+"' "+
                            (gen_data.data.def.indexOf(parseInt(element.id))!=-1?"checked":"")
                            +" "+gen_data.input_opt+">"+element.name+
                    "</label></div></div>";
        });
        if(gen_data.input_opt != 'disabled')
            html += '<div class="input-group">\
                        <input type="text" class="form-control" placeholder="Nowy enum">\
                        <span class="input-group-btn">\
                            <button class="btn btn-success '+gen_data.id+'-enum-add" type="button">Dodaj</button>\
                        </span>\
                    </div>';
        return html;
    }
    /*
     * Zwracanie enumeratora
     * @param   name        Nazwa enumeratora w tabeli
     * @param   row         JSON z odpowiedzi serwera zawierający informacje o enumie
     * @param   input_opt   Opcje na enum
     */
    var gen_table = {};
    gen_table[_.SettingEnum.MULTI_ENUM]  = getMultipleEnum;
    gen_table[_.SettingEnum.SINGLE_ENUM] = getSingleEnum;
    var getEnum = function(name, row, id, input_opt) {
        var html      = '',
            enum_data = new EnumData(
                name, 
                row['table'], row['data'], 
                row['value'], row['entry_col']),
            gen_data  = new GenData(enum_data, id, input_opt);
        /* Szukanie domyślnej wartości, jeśli nie ma to dodaje */
        return gen_table[row['type']](gen_data)+
                '<input type="hidden" name="enum_table" value="'+
                    enum_data.table+
                '">'+html;
    }
    return {
        EnumData    :   EnumData,
        /* Funkcje */
        getEnum     :   getEnum
    };
});