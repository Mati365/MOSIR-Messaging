/* 
 * Narzędzia wykorzystywane do
 * renderingu strony
 */
define('inbox/utils', 
        [
            'jquery',
            'inbox/inbox'
        ], 
        function($, _) {
    /* 
     * Na podstawie przycisku wciśniętego
     * zwracany jest obiekt inboxa
     * @param   obj     Przycisk
     * @return  Obiekt inbox
     */
    var parentInbox = function(obj) {
        return _.Inbox.inboxes[obj
                .closest('table')
                .attr('id')];
    };
    /* 
     * Na podstawie przycisku wciśniętego
     * zwracany jest index wiadomości
     * @param   obj     Przycisk
     * @return  Index wiadomości
     */
    var messageIndex = function(col) {
        return parseInt(col.closest('tr')
                .find('td:eq(1)')
                .text());
    };
    /* 
     * Kasowanie wiadomości
     * @param   obj     Przycisk
     * @return  perm    Pernamentnie
     */
    var remove = function(obj, perm) {
        var inbox = parentInbox($(obj));
        inbox.forEach('remove', [ perm ]);
        setTimeout(function() {
            inbox.refresh(true);
        }, 700)
    };
    return {
        /* Funkcje */
        parentInbox     :   parentInbox,
        messageIndex    :   messageIndex,
        remove          :   remove,
        /* 
         * Settings nie przypisany do inboxa 
         * bo filtr jest dla wszystkich inboxów 
         */
        settings        :   []
    };
});