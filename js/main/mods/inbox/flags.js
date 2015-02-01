/* 
 * Flagi skrzynki pocztowej
 */
define('inbox/flags', 
        [
            'utils'
        ], 
        function(_utils) {
    /* Typy skrzynki */
    var InboxType   = _utils.enum([
                'INBOX',
                'OUTBOX'
            ], 2);
    /* Flagi wiadomości */
    var MessageFlag = _utils.enum([
                'VIEWED',
                'REMOVED',
                'STARRED',
                'REPLY',
                'DONE',
                'GENERATED',
            ], 1, true);
    return {
        InboxType       :   InboxType,
        MessageFlag     :   MessageFlag
    };
});