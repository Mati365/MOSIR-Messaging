/**
 * Narzędzia wykorzystywane do
 * renderingu strony
 */
define('page', 
        [
            'jquery'
        ], 
        function($) {
    /**
     * Usuwanie atrybutu nieprzecztanej
     * wiadomości
     * @param   self        Dany tekst
     * @param   css_class   Klasa blink w css
     */
    var disableBlinking = function(self, css_class) {
        self.parent().children().each(function() { 
            $(this).removeClass(css_class); 
        });
    };
    /**
     * Kopiowanie panelu odpowiedzi zwrotnej,
     * jeśli istnieje to kasowanie
     */
    var getReplyForm = function(obj) {
        var clone = $( "#report-comment-box" )
                        .children()
                        .clone();
        $(clone)
            .eq(0)
            .removeAttr('id')
            .addClass('mail-comment')
            .html('');  
        return clone;
    };
    return {
        disableBlinking     :   disableBlinking,
        getReplyForm        :   getReplyForm
    };
});