BX.ready(function () {
    BX.Event.EventEmitter.subscribe('onEntityCreate', sidePanelPostMessage);
    BX.Event.EventEmitter.subscribe('onEntityUpdate', sidePanelPostMessage);

    function sidePanelPostMessage() {
        BX.SidePanel.Instance.postMessage(
            window,
            'booksUpdate',
            {booksUpdate: true}
        );
    }
});
