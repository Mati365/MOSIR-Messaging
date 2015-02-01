/*
 * Narzędzia dla JS wspomagające
 * OOP itp.
 */
define('utils', 
        [
            'jquery'
        ], 
        function($) {
    var truncate = function(str, len) {
        return str.length>len?(str.substring(0, len)+'...'):str;
    };
    var boolToInt = function(val) {
        return val ? 1 : 0;
    };
    /*
     * Powiększanie pierwszego znaku
     * @param    str    Tekst
     * @return    Tekst
     */
    var toUpperFirst = function(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    };
    /*
     * Używana do zwracania czasu w milisekundach
     * @return            czas
     */
    var currentTime = function() {
        return new Date();
    };
    /*
     * Używana do testowania flag wartości
     * @param    value    Obiekt testowany
     * @param    flag    Flaga
     * @return    Zawartość flag
     */
    var isSetFlag = function(value, flag) {
        return (value & flag) == flag;
    };
    /*
     * Używana do generowania singleton dla klas
     * @param     class    klasa singleton
     * @return            Namespace
     */
    var singleton = function(_class, _init) {
        _class.getInstance = function() {
            if(!this.isSet(_class.instance))
                _class.instance = new _class;
            return _class.instance;
        };
        if(this.isSet(_init))
            _class.getInstance();
        return this;
    };
    /*
     * Używana do zgłaszania wyjątków 
     * @param     condition    Jeśli false wywala wyjątek
     * @param    exception    Treść wyjątku
     * @return                Namespace
     */
    var assert = function(condition, exception) {
        if(Array.isArray(condition))
            for(var k in condition)
                if(!condition[k])
                    return new Error(exception);
        if(!condition)
            throw new Error(exception);
        return this;
    };
    /*
     * TODO: Przepisanie na JQuery
     * Używana do dołączania kolejnych plików js
     * @param     url            Adres wczytywanego pliku
     * @param    callback    Funkcja wywoływana po wczytwaniu pliku
     * @return                Namespace
     */
    var include = function(url, callback) {
        var body    = document.getElementsByTagName('head')[0],
            script  = document.createElement('script');

        script.type= 'text/javascript';
        script.src = url;
        script.onload = function() { 
            callback(url);
        };
        body.insertBefore(script, body.firstChild);
        return this;
    };
    /*
     * Używana do validacji obiektu
     * @param    obj        Obiekt testowany
     * @return            Output validacji
     */
    var isSet = function(obj, method) {
        if(!obj)
            return false;
        if(Array.isArray(obj)) {
            for(var k in obj)
                if(!obj[k] || typeof obj[k] === "undefined")
                    return false;
            return true;
        } else if(typeof method === "undefined")
            return typeof obj !== "undefined";
        else
            return typeof obj[method] !== "undefined";
    };
    /*
     * Klonuje obiekt
     * @param    obj    Obiekt klonowany
     * @return        Namespace
     */
    var clone = function(obj, override) {
        if(typeof obj === "undefined")
            return obj;
        var _clone = Array.isArray(obj) ? [ ] : { };
        for(var k in obj)
            _clone[k] = typeof obj[k] === "object" ? 
                                        this.clone(obj[k]) : 
                                        obj[k];
        if(typeof override !== "undefined")
            this.copy(override, _clone);
        return _clone;
    };
    /*
     * Tworzy obiekt ze string
     * @param    text    Tekst
     * @return    Obiekt
     */
    var create = function(text) {
        var parts = text.split("."),
            obj   = obj;
        for (var i = 0, len = parts.length, obj = window; i < len; ++i)
            obj = obj[parts[i]];
        return new obj;
    };
    /*
     * Kopiuje do innego
     * @param    from    Obiekt kopiowany
     * @param    to        Obiekt do którego ma być skopiowany
     * @return            Namespace
     */
    var copy = function(from, to) {
        if(!this.isSet([from,to]))
            return to;
        for (var property in from)
            to[property] = from[property];
        return to;
    };
    /*
     * Czyści obiekt
     * @param    obj    Obiekt czyszczony
     * @return        Namespace
     */
    var clear = function(obj) {
        for (var prop in obj)
            delete obj[prop]; 
        return this;
    };
    /*
     * class tworzy pusty obiekt, zaś extend 
     * używana do dziedziczenia obiektów
     * @param    source        Obiekt nadrzędny
     * @param    destination    Obiekt dziedziczący
     * @return                Namespace
     */
    var extend = function(source, destination, proto) {
        if(this.isSet(proto)) {
            destination.prototype             = source;
            destination.prototype.constructor = destination;
        } else
            this.copy(source, destination);
        return this;
    };
    var classFactory = function(parent, member_list, gen_object) {
        var _C_TEMP = null;
        /* Generuje pustą klasę z polami i konstruktorem do wartości */
        if(!this.isSet([member_list, gen_object]) && 
                Array.isArray(parent)) {
            _C_TEMP = function() {
                for(var k in arguments)
                    this[parent[k]] = arguments[k];
            };
            for(var k in parent)
                _C_TEMP.prototype[parent[k]] = 0;
            return _C_TEMP;
        }
        /* Normalna klasa z własnym konstruktorem */
        _C_TEMP = this.isSet(member_list) ? 
                                member_list : 
                                function() {};
        /* Jeśli ma rodzica to dziedziczy */
        if(this.isSet(parent))
            this.extend(parent, _C_TEMP, true);
        
        /* Dodatkowe metody */
        _C_TEMP.prototype.clone = function() {
            return this.clone(this);
        };
        _C_TEMP.prototype.setParams = function(params) {
            for(var k in params)
                if(this.isSet(this, k))
                    this[k] = params[k];
            return this;
        };
        _C_TEMP.prototype.set = function(key, value) {
            this[key] = value;
            return this;
        };
        _C_TEMP.prototype.get = function(key) {
            return this[key];
        };
        if(this.isSet(gen_object))
            return new _C_TEMP;
        return _C_TEMP;
    };
    /*
     * Zwraca generowanie enumy
     * @param    values        Nazwy zmienynch enuma
     * @param    bit_flag    (Opcjonalny) flagi bitowe
     */
    var enumFactory = function(values, first_val, bit_flag) {
        var _enum   = { };
        for(var i = 0;i < values.length;++i) {
            var _margin = i + (this.isSet(first_val) ? first_val : 0);
            _enum[values[i]] = (this.isSet(bit_flag) ? (1 << _margin) : _margin);
        }
        return _enum;
    };
    /*
     * Używania do kolejnego                         Wczytywania elementów
     * @param    path_list                            Lista ścieżek
     * @param    loader(url, index, done)            Funkcja wczytująca
     * @param    callback                            Callback po wczytaniu
     * @return                                        Namespace
     */
    var loadFiles = function(path_list, loader, callback) {
        this.assert(this.isSet([path_list, loader]), "Critical loader error!");
        var files = [ ];
        (function ret(l, path_list, loader) {
            if(l != path_list.length)
                loader(path_list[l], function(loaded_obj) { 
                    files.push(loaded_obj);
                    ret(++l, path_list, loader);
                });
            else if(this.isSet(callback))
                callback(files);
        }(0, path_list, loader));
    };
    /*
     * Używana    do wczytywania listy zależności
     * @param    lib_list    Lista bibliotek
     * @param    callback    Funkcja wywoływana po wczytaniu libów
     * @return                Namespace
     */
    var loadLibs = function(lib_list, callback) {
        this.loadFiles(lib_list, 
                       this.include, 
                       callback);
        return this;
    };
    /*
     * Używana do generowania setterów/getterów
     * @param    obj        Klasa obiektu, w którym się generuje jeśli
     *                     w nazwie obiektu do getta jest string
     *                     to odwołuje się do this
     * @param    prop    Nazwa gettera/settera
     * @param    value    Składowa dla gettera, settera
     * @return            Namespace
     */
    var genGetterSetter = function(obj, variables, getter, setter, k) {
        k = this.getSafeParam(k, 0);
        if(k >= variables.length)
            return this;
        var variable = variables[k][0],
            name     = variables[k][1],
            public   = (typeof variable === "string");
        if(typeof setter === "undefined" || setter)
            obj['set' + name] = function(_v) {
                if(public)
                    this[variable] = _v;
                else
                    variable = _v;
                return this;
            };
        if(typeof getter === "undefined" || getter)
            obj['get' + name] = function() {
                if(!variable || typeof variable === "undefined")
                    return null;
                if(typeof variable.value !== "undefined")    
                    return variable.value;
                else if(typeof variable.valueOf !== "undefined")
                    return variable.valueOf();
                else
                    return public ? this[variable] : variable;
            };
        this.genGetterSetter(
                    obj, variables, 
                    getter, setter, 
                    ++k);
        return this;
    };
    /*
     * Zwraca bezpieczny typ, jeśli undefined to null
     * @param    param    Obiekt
     * @return    Bezpieczny obiekt
     */
    var getSafeParam = function(param, def_value) {
        return param || def_value;
    };
    /*
     * Przerabia zwykłe obiekty na tablice
     * @param    param    Obiekt
     * @return    Tablica
     */
    var getSafeArray = function(param) {
        return Array.isArray(param) ? param : [ param ];
    };
    /*
     * Zwraca rozszerzenie pliku
     * @param    url    Ścieżka do pliku
     * @return    Rozszerzenie
     */
    var getFileExtension = function(url) {
        return url.match(/.*\.(\w*)/)[1];
    };
    return {
        truncate    :   truncate,
        boolToInt   :   boolToInt,
        toUpperFirst:   toUpperFirst,
        currentTime :   currentTime,
        isSetFlag   :   isSetFlag,
        /* Większe funkcje */
        singleton   :   singleton,
        assert      :   assert,
        include     :   include,
        isSet       :   isSet,
        clone       :   clone,
        create      :   create,
        /* Wyspecjalizowane */
        copy     :    copy,
        clear    :    clear,
        extend   :    extend,
        class    :    classFactory,
        enum     :    enumFactory,
        genGetterSetter : genGetterSetter,
        /* Zasoby */
        loadFiles   :    loadFiles,
        loadLibs    :    loadLibs,
        /* Bezpieczne parametry */
        getSafeParam    :   getSafeParam,
        getSafeArray    :   getSafeArray,
        getFileExtension:   getFileExtension,
        /* Testy wydajności funkcji */
        Benchmark       :   {
            last_time    :    0,
            /* Początek testu */
            begin        :    function() {
                last_time = currentTime();
            },
            /*
             * Koniec testu
             * @return    Czas w ms
             */
            end          :    function(text) {
                var t = currentTime() - last_time;
                if(isSet(text))
                    console.log(text + " " + t + "ms");
                return t;
            },
        }
    };
});