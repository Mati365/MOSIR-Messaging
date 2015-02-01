/* 
 * Flagi skrzynki pocztowej
 */
define('admin/flags', 
        [
            'utils'
        ], 
        function(_utils) {
    /* Typy skrzynki */
    var SettingEnum = _utils.enum([
        'MULTI_ENUM',   /* Wiele checkboxów export. do jsona */
        'SINGLE_ENUM'   /* Jeden o określonej wartości */
    ]);
    return {
        SettingEnum     :   SettingEnum
    };
});